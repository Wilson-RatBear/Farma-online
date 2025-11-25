<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ItemPedido;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    // Crear nuevo pedido desde el carrito
    public function store(Request $request)
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
                'metodo_pago' => 'required|in:tarjeta,efectivo',
                'notas' => 'nullable|string|max:1000'
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

            // Crear el pedido
            $pedido = Pedido::create([
                'usuario_id' => $usuario->id,
                'numero_orden' => Pedido::generarNumeroOrden(),
                'total' => $total,
                'estado' => 'pendiente',
                'direccion_envio' => $request->direccion_envio,
                'ciudad_envio' => $request->ciudad_envio,
                'telefono_contacto' => $request->telefono_contacto,
                'metodo_pago' => $request->metodo_pago,
            ]);

            // Crear items del pedido desde el carrito
            foreach ($carritoItems as $carritoItem) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $carritoItem->producto_id,
                    'cantidad' => $carritoItem->cantidad,
                    'precio_unitario' => $carritoItem->precio_unitario
                ]);

                // Actualizar stock del producto (opcional)
                $producto = Producto::find($carritoItem->producto_id);
                if ($producto) {
                    $producto->decrement('stock', $carritoItem->cantidad);
                }
            }

            // Vaciar carrito
            Carrito::where('usuario_id', $usuario->id)->delete();

            DB::commit();

            // Cargar relaciones para la respuesta
            $pedido->load(['items.producto.categoria']);

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido,
                'numero_orden' => $pedido->numero_orden
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
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
}