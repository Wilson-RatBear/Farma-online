<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Obtener historial de pedidos del usuario
     */
    public function index(Request $request)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $pedidos = Pedido::with('items.producto.categoria')
                            ->where('usuario_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos,
                'count' => $pedidos->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo pedido desde el carrito (checkout)
     */
    public function store(Request $request)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $request->validate([
                'direccion_envio' => 'required|string',
                'ciudad_envio' => 'required|string',
                'telefono_contacto' => 'required|string',
                'metodo_pago' => 'required|in:tarjeta,efectivo'
            ]);

            // Verificar que el carrito no esté vacío
            $carritoItems = Carrito::with('producto')
                                ->where('usuario_id', $user->id)
                                ->get();

            if ($carritoItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ], 400);
            }

            // Calcular total
            $total = $carritoItems->sum(function ($item) {
                return $item->cantidad * $item->precio_unitario;
            });

            // Crear pedido en transacción
            DB::beginTransaction();

            try {
                // Crear pedido
                $pedido = Pedido::create([
                    'usuario_id' => $user->id,
                    'numero_orden' => Pedido::generarNumeroOrden(),
                    'total' => $total,
                    'estado' => Pedido::ESTADO_PENDIENTE,
                    'direccion_envio' => $request->direccion_envio,
                    'ciudad_envio' => $request->ciudad_envio,
                    'telefono_contacto' => $request->telefono_contacto,
                    'metodo_pago' => $request->metodo_pago
                ]);

                // Crear items del pedido desde el carrito
                foreach ($carritoItems as $carritoItem) {
                    ItemPedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $carritoItem->producto_id,
                        'cantidad' => $carritoItem->cantidad,
                        'precio_unitario' => $carritoItem->precio_unitario
                    ]);
                }

                // Vaciar carrito
                Carrito::where('usuario_id', $user->id)->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido creado exitosamente',
                    'pedido' => $pedido->load('items.producto.categoria')
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de un pedido específico
     */
    public function show(Request $request, $id)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $pedido = Pedido::with('items.producto.categoria')
                            ->where('usuario_id', $user->id)
                            ->where('id', $id)
                            ->firstOrFail();

            return response()->json([
                'success' => true,
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar pedido (solo si está pendiente)
     */
    public function cancelar(Request $request, $id)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $pedido = Pedido::where('usuario_id', $user->id)
                            ->where('id', $id)
                            ->firstOrFail();

            // Solo se puede cancelar pedidos pendientes
            if ($pedido->estado !== Pedido::ESTADO_PENDIENTE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar pedidos pendientes'
                ], 400);
            }

            $pedido->estado = Pedido::ESTADO_CANCELADO;
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente',
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}