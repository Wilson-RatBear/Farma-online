<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Categoria;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Obtener métricas generales del dashboard
     */
    public function metricasGenerales(): JsonResponse
    {
        try {
            // Obtener fecha actual y inicio del mes
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfDay = $now->copy()->startOfDay();

            // Métricas principales
            $metrics = [
                // Ingresos totales (todos los pedidos confirmados/enviados/entregados)
                'ingresos_totales' => Pedido::whereIn('estado', ['confirmado', 'enviado', 'entregado'])
                                            ->sum('total'),
                
                // Total de pedidos
                'pedidos_totales' => Pedido::count(),
                
                // Total de usuarios registrados
                'usuarios_totales' => User::count(),
                
                // Total de productos activos
                'productos_totales' => Producto::where('activo', true)->count(),
                
                // Ingresos del mes actual
                'ingresos_mensuales' => Pedido::whereIn('estado', ['confirmado', 'enviado', 'entregado'])
                                             ->where('created_at', '>=', $startOfMonth)
                                             ->sum('total'),
                
                // Pedidos del mes actual
                'pedidos_mensuales' => Pedido::where('created_at', '>=', $startOfMonth)->count(),
                
                // Pedidos de hoy
                'pedidos_hoy' => Pedido::where('created_at', '>=', $startOfDay)->count(),
                
                // Ingresos de hoy
                'ingresos_hoy' => Pedido::whereIn('estado', ['confirmado', 'enviado', 'entregado'])
                                       ->where('created_at', '>=', $startOfDay)
                                       ->sum('total'),
                
                // Productos con stock bajo (menos de 10 unidades)
                'productos_stock_bajo' => Producto::where('stock', '<', 10)
                                                 ->where('activo', true)
                                                 ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }
}