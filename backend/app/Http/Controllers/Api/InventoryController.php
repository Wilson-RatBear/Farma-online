<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // Obtener productos con stock bajo
    public function stockBajo()
    {
        try {
            $productos = Producto::with(['categoria', 'proveedor'])
            ->where('stock', '<', 10)
            ->where('activo', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $productos,
            'total' => $productos->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener productos con stock bajo',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Obtener estadísticas de inventario
    public function estadisticas()
{
    try {
        $stats = [
            'total_productos' => Producto::where('activo', true)->count(),
            'productos_stock_bajo' => Producto::where('stock', '<', 10)->where('activo', true)->count(),
            'productos_sin_stock' => Producto::where('stock', 0)->where('activo', true)->count(),
            'valor_inventario_total' => Producto::where('activo', true)
                ->get()
                ->sum(function($producto) {
                    return $producto->stock * $producto->precio;
                }),
            'proveedores_activos' => Proveedor::where('activo', true)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
        ], 500);
    }
}

    // Registrar movimiento de inventario
    public function registrarMovimiento(Request $request)
    {
        try {
            $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'tipo' => 'required|in:entrada,salida,ajuste',
                'cantidad' => 'required|integer|min:1',
                'motivo' => 'required|string|max:255',
                'observaciones' => 'nullable|string',
                'proveedor_id' => 'nullable|exists:proveedores,id'
            ]);

            DB::transaction(function () use ($request) {
                $producto = Producto::findOrFail($request->producto_id);
                $stockAnterior = $producto->stock;

                // Calcular nuevo stock según el tipo
                if ($request->tipo === 'entrada') {
                    $stockNuevo = $stockAnterior + $request->cantidad;
                } elseif ($request->tipo === 'salida') {
                    $stockNuevo = $stockAnterior - $request->cantidad;
                } else { // ajuste
                    $stockNuevo = $request->cantidad;
                }

                // Actualizar stock del producto
                $producto->stock = $stockNuevo;
                if ($request->tipo === 'entrada') {
                    $producto->ultima_reposicion = now();
                }
                $producto->save();

                // Registrar movimiento
                MovimientoInventario::create([
                    'producto_id' => $request->producto_id,
                    'tipo' => $request->tipo,
                    'cantidad' => $request->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'motivo' => $request->motivo,
                    'observaciones' => $request->observaciones,
                    'usuario_id' => auth()->id(),
                    'proveedor_id' => $request->proveedor_id
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar movimiento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener historial de movimientos
    public function historialMovimientos(Request $request)
    {
        try {
            $query = MovimientoInventario::with(['producto', 'usuario', 'proveedor'])
                ->orderBy('created_at', 'desc');

            // Filtrar por producto si se especifica
            if ($request->has('producto_id')) {
                $query->where('producto_id', $request->producto_id);
            }

            // Filtrar por tipo si se especifica
            if ($request->has('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            // Filtrar por rango de fechas
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('created_at', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            }

            $movimientos = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $movimientos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}