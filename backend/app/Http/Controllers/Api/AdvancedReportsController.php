<?php
// app/Http/Controllers/Api/AdvancedReportsController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedReportsController extends Controller
{
    // Reporte de ventas por período
    public function ventasPorPeriodo(Request $request)
    {
        try {
            $rango = $request->get('rango', 'mensual'); // diario, semanal, mensual, anual
            
            $query = DB::table('pedidos')
                ->whereIn('estado', ['confirmado', 'enviado', 'entregado']);
            
            switch($rango) {
                case 'diario':
                    $query->select(
                        DB::raw('DATE(created_at) as fecha'),
                        DB::raw('COUNT(*) as total_pedidos'),
                        DB::raw('SUM(total) as ingresos_totales')
                    )->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('fecha', 'DESC')
                    ->limit(30);
                    break;
                    
                case 'semanal':
                    $query->select(
                        DB::raw('YEAR(created_at) as año'),
                        DB::raw('WEEK(created_at) as semana'),
                        DB::raw('COUNT(*) as total_pedidos'),
                        DB::raw('SUM(total) as ingresos_totales')
                    )->groupBy('año', 'semana')
                    ->orderBy('año', 'DESC')
                    ->orderBy('semana', 'DESC')
                    ->limit(12);
                    break;
                    
                case 'mensual':
                default:
                    $query->select(
                        DB::raw('YEAR(created_at) as año'),
                        DB::raw('MONTH(created_at) as mes'),
                        DB::raw('COUNT(*) as total_pedidos'),
                        DB::raw('SUM(total) as ingresos_totales')
                    )->groupBy('año', 'mes')
                    ->orderBy('año', 'DESC')
                    ->orderBy('mes', 'DESC')
                    ->limit(12);
                    break;
            }
            
            $datos = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $datos,
                'rango' => $rango
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    // Productos más vendidos
    public function productosMasVendidos(Request $request)
    {
        try {
            $limite = $request->get('limite', 10);
            $periodo = $request->get('periodo', 'todo'); // semana, mes, año, todo
            
            $query = DB::table('items_pedido as ip')
                ->join('productos as p', 'ip.producto_id', '=', 'p.id')
                ->join('pedidos as ped', 'ip.pedido_id', '=', 'ped.id')
                ->whereIn('ped.estado', ['confirmado', 'enviado', 'entregado'])
                ->select(
                    'p.id',
                    'p.nombre',
                    'p.precio',
                    DB::raw('SUM(ip.cantidad) as total_vendido'),
                    DB::raw('SUM(ip.cantidad * ip.precio_unitario) as ingresos_generados')
                )
                ->groupBy('p.id', 'p.nombre', 'p.precio')
                ->orderBy('total_vendido', 'DESC')
                ->limit($limite);
            
            // Filtrar por período
            if ($periodo !== 'todo') {
                $fechaFiltro = Carbon::now();
                switch($periodo) {
                    case 'semana':
                        $fechaFiltro->subWeek();
                        break;
                    case 'mes':
                        $fechaFiltro->subMonth();
                        break;
                    case 'año':
                        $fechaFiltro->subYear();
                        break;
                }
                $query->where('ped.created_at', '>=', $fechaFiltro);
            }
            
            $productos = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $productos,
                'periodo' => $periodo
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métricas de usuarios
    public function metricasUsuarios()
    {
        try {
            $totalUsuarios = DB::table('users')->count();
            $usuariosNuevosEsteMes = DB::table('users')
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();
            $usuariosActivos = DB::table('users')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('pedidos')
                          ->whereColumn('pedidos.usuario_id', 'users.id')
                          ->whereIn('pedidos.estado', ['confirmado', 'enviado', 'entregado']);
                })
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_usuarios' => $totalUsuarios,
                    'usuarios_nuevos_mes' => $usuariosNuevosEsteMes,
                    'usuarios_activos' => $usuariosActivos,
                    'tasa_actividad' => $totalUsuarios > 0 ? round(($usuariosActivos / $totalUsuarios) * 100, 2) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar métricas de usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // Estadísticas de categorías
    public function estadisticasCategorias()
    {
        try {
            $categorias = DB::table('categorias as c')
                ->leftJoin('productos as p', 'c.id', '=', 'p.categoria_id')
                ->leftJoin('items_pedido as ip', 'p.id', '=', 'ip.producto_id')
                ->leftJoin('pedidos as ped', 'ip.pedido_id', '=', 'ped.id')
                ->whereIn('ped.estado', ['confirmado', 'enviado', 'entregado'])
                ->orWhereNull('ped.estado')
                ->select(
                    'c.id',
                    'c.nombre',
                    DB::raw('COUNT(DISTINCT p.id) as total_productos'),
                    DB::raw('SUM(COALESCE(ip.cantidad, 0)) as total_vendido'),
                    DB::raw('SUM(COALESCE(ip.cantidad * ip.precio_unitario, 0)) as ingresos_generados')
                )
                ->groupBy('c.id', 'c.nombre')
                ->orderBy('ingresos_generados', 'DESC')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar estadísticas de categorías: ' . $e->getMessage()
            ], 500);
        }
    }
}