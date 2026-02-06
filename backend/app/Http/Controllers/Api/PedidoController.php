<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Support\Facades\Mail;

class PedidoController extends Controller
{
    // Obtener métodos de pago disponibles
    public function metodosPago()
{
    $metodos = [
        [
            'codigo' => 'pago_movil',
            'nombre' => 'Pago Móvil',
            'bancos' => ['BDV', 'Banesco', 'Mercantil', 'Venezuela', 'Bancaribe'],
            'requiere_referencia' => true,
            'requiere_telefono' => true,
            'requiere_banco' => true
        ],
        [
            'codigo' => 'efectivo',
            'nombre' => 'Pago en Efectivo',
            'bancos' => [],
            'requiere_referencia' => false,
            'requiere_telefono' => false,
            'requiere_banco' => false
        ]
    ];

    return response()->json([
        'success' => true,
        'metodos' => $metodos
    ]);
}

    // Crear pedido con información de pago real (NUEVO MÉTODO)
    public function checkoutConPago(Request $request)
    {
        try {
            DB::beginTransaction();

            // Obtener usuario autenticado
            $usuario = auth()->user();
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Validar datos de envío y pago
            $request->validate([
                'direccion_envio' => 'required|string|max:500',
                'ciudad_envio' => 'required|string|max:100',
                'telefono_contacto' => 'required|string|max:20',
                'metodo_pago' => 'required|in:pago_movil,transferencia,zelle,paypal,efectivo',
                'referencia_pago' => 'required_if:metodo_pago,pago_movil,transferencia,zelle|string|max:100',
                'banco' => 'required_if:metodo_pago,pago_movil,transferencia|string|max:50',
                'telefono_pago' => 'required_if:metodo_pago,pago_movil|string|max:20',
            ]);

            // Obtener items del carrito del usuario
            $carritoItems = Carrito::where('usuario_id', $usuario->id)->get();
            
            if ($carritoItems->isEmpty()) {
                return response()->json(['error' => 'El carrito está vacío'], 400);
            }

            // Calcular total
            $total = 0;
            foreach ($carritoItems as $item) {
                $total += $item->cantidad * $item->precio_unitario;
            }

            // Determinar estados según método de pago
            $estadoPedido = $request->metodo_pago === 'efectivo' ? 'confirmado' : 'pendiente';
            $estadoPago = $request->metodo_pago === 'efectivo' ? 'verificado' : 'pendiente';

            // Crear el pedido con información de pago
            $pedido = Pedido::create([
                'usuario_id' => $usuario->id,
                'numero_orden' => 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -6)),
                'total' => $total,
                'estado' => $estadoPedido,
                'direccion_envio' => $request->direccion_envio,
                'ciudad_envio' => $request->ciudad_envio,
                'telefono_contacto' => $request->telefono_contacto,
                'metodo_pago' => $request->metodo_pago,
                'metodo_pago_detalle' => $this->getMetodoPagoNombre($request->metodo_pago),
                'referencia_pago' => $request->referencia_pago,
                'banco' => $request->banco,
                'telefono_pago' => $request->telefono_pago,
                'monto_pagado' => $total,
                'estado_pago' => $estadoPago,
                'fecha_pago' => $request->metodo_pago === 'efectivo' ? now() : null
            ]);

            // Crear items del pedido desde el carrito
            foreach ($carritoItems as $carritoItem) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $carritoItem->producto_id,
                    'cantidad' => $carritoItem->cantidad,
                    'precio_unitario' => $carritoItem->precio_unitario
                ]);

                // Actualizar stock del producto
                $producto = Producto::find($carritoItem->producto_id);
                if ($producto) {
                    $producto->decrement('stock', $carritoItem->cantidad);
                }
            }

            // Crear registro de pago si no es efectivo
            if ($request->metodo_pago !== 'efectivo') {
                Pago::create([
                    'pedido_id' => $pedido->id,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia' => $request->referencia_pago,
                    'banco' => $request->banco,
                    'telefono' => $request->telefono_pago,
                    'monto' => $total,
                    'estado' => 'pendiente'
                ]);
            }

            // ✅ ENVIAR EMAIL DE CONFIRMACIÓN
            try {
                Mail::to($usuario->email)->send(new OrderConfirmationMail($pedido));
                \Log::info('Email de confirmación enviado a: ' . $usuario->email);
            } catch (\Exception $e) {
                \Log::error('Error enviando email: ' . $e->getMessage());
                // No falla el pedido si el email falla
            }

            // Vaciar carrito
            Carrito::where('usuario_id', $usuario->id)->delete();

            DB::commit();

            // Cargar relaciones para la respuesta
            $pedido->load(['items.producto.categoria', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => $request->metodo_pago === 'efectivo' 
                    ? 'Pedido creado exitosamente' 
                    : 'Pedido creado. Por favor verifique el pago.',
                'pedido' => $pedido,
                'numero_orden' => $pedido->numero_orden,
                'email_enviado' => true
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en checkout con pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al crear el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener historial de pedidos del usuario
    public function index()
    {
        try {
            $usuario = auth()->user();
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $pedidos = Pedido::with(['items.producto.categoria'])
                ->where('usuario_id', $usuario->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'pedidos' => $pedidos,
                'total' => $pedidos->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener detalle de un pedido específico
    public function show($id)
    {
        try {
            $usuario = auth()->user();
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $pedido = Pedido::with(['items.producto.categoria', 'usuario'])
                ->where('usuario_id', $usuario->id)
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Pedido no encontrado'
            ], 404);
        }
    }
    

    /**
     * Actualizar estado de pedido y enviar notificación
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // ✅ CORREGIDO: Cambiar 'user' por 'usuario'
            $pedido = Pedido::with(['usuario', 'items.producto'])->find($id);
            
            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            $request->validate([
                'estado' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado'
            ]);

            $estadoAnterior = $pedido->estado;
            $nuevoEstado = $request->estado;

            // Actualizar estado
            $pedido->estado = $nuevoEstado;
            $pedido->save();

            // Enviar email de notificación
            try {
                // ✅ CORREGIDO: Cambiar 'user' por 'usuario'
                Mail::to($pedido->usuario->email)->send(new OrderStatusUpdatedMail($pedido, $nuevoEstado, $estadoAnterior));
                
                // ✅ CORREGIDO: Cambiar 'user' por 'usuario'
                \Log::info("Email de cambio de estado enviado para pedido #{$pedido->numero_orden} a {$pedido->usuario->email}");
            } catch (\Exception $emailError) {
                \Log::error("Error enviando email de cambio de estado: " . $emailError->getMessage());
                // No falla la operación principal si el email falla
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado del pedido actualizado correctamente',
                'pedido' => $pedido,
                'email_enviado' => true
            ]);

        } catch (\Exception $e) {
            \Log::error("Error actualizando estado del pedido: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado del pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener nombre del método de pago
     */
    private function getMetodoPagoNombre($codigo)
    {
        $nombres = [
            'pago_movil' => 'Pago Móvil',
            'transferencia' => 'Transferencia Bancaria',
            'zelle' => 'Zelle',
            'paypal' => 'PayPal',
            'efectivo' => 'Pago en Efectivo',
            'movil' => 'Pago Móvil'
        ];

        return $nombres[$codigo] ?? $codigo;
    }
}