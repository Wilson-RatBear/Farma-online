<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    // Listar todos los proveedores
    public function index()
{
    try {
        $proveedores = Proveedor::withCount('productos')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proveedores
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener proveedores: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}

    // Crear nuevo proveedor
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'contacto' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'direccion' => 'nullable|string',
                'tiempo_entrega_dias' => 'nullable|integer|min:1'
            ]);

            $proveedor = Proveedor::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado correctamente',
                'data' => $proveedor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Actualizar proveedor
    public function update(Request $request, $id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:255',
                'contacto' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'direccion' => 'nullable|string',
                'tiempo_entrega_dias' => 'nullable|integer|min:1',
                'activo' => 'boolean'
            ]);

            $proveedor->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado correctamente',
                'data' => $proveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar proveedor (soft delete)
    public function destroy($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);

            // Verificar si tiene productos asociados
            if ($proveedor->productos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene productos asociados'
                ], 400);
            }

            $proveedor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Obtener estadísticas de proveedores
    public function estadisticas()
    {
        try {
            $stats = [
                'total_proveedores' => Proveedor::count(),
                'proveedores_activos' => Proveedor::activos()->count(),
                'proveedores_inactivos' => Proveedor::where('activo', false)->count(),
                'proveedor_mas_productos' => Proveedor::withCount('productos')
                    ->orderBy('productos_count', 'desc')
                    ->first()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}