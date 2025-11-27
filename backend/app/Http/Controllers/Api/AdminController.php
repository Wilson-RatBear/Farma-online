<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Obtener estadísticas del dashboard
     */
    public function dashboard()
    {
        try {
            $stats = [
                'total_usuarios' => User::count(),
                'total_productos' => Producto::count(),
                'total_pedidos' => Pedido::count(),
                'pedidos_pendientes' => Pedido::where('estado', 'pendiente')->count(),
                'pedidos_confirmados' => Pedido::where('estado', 'confirmado')->count(),
                'pedidos_enviados' => Pedido::where('estado', 'enviado')->count(),
                'pedidos_entregados' => Pedido::where('estado', 'entregado')->count(),
                'ingresos_totales' => Pedido::where('estado', '!=', 'cancelado')->sum('total'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los pedidos (para administrador)
     */
    public function getAllOrders(Request $request) // ✅ AGREGAR Request $request
    {
        try {
            $pedidos = Pedido::with(['usuario', 'items.producto.categoria'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getAllOrders: ' . $e->getMessage()); // ✅ AGREGAR LOG
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de un pedido
     */
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'estado' => 'required|in:pendiente,confirmado,enviado,entregado,cancelado'
            ]);

            $pedido = Pedido::findOrFail($id);
            $pedido->estado = $request->estado;
            $pedido->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado del pedido actualizado correctamente',
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }
}