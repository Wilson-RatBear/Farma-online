<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::withCount('productos')->get();
        return response()->json($categorias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ ACTUALIZAR este método (no dejar el 501)
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:categorias,nombre',
                'descripcion' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:120|unique:categorias,slug'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categoria = Categoria::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'slug' => $request->slug ?? \Illuminate\Support\Str::slug($request->nombre)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'categoria' => $categoria
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $categoria = Categoria::with('productos')->find($id);
        
        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // ✅ ACTUALIZAR este método (no dejar el 501)
        try {
            $categoria = Categoria::find($id);
            
            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id,
                'descripcion' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:120|unique:categorias,slug,' . $id
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $categoria->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'slug' => $request->slug ?? \Illuminate\Support\Str::slug($request->nombre)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'categoria' => $categoria
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // ✅ ACTUALIZAR este método (no dejar el 501)
        try {
            $categoria = Categoria::find($id);
            
            if (!$categoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            // Verificar si la categoría tiene productos
            if ($categoria->productos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene productos asociados. Reasigna los productos primero.'
                ], 422);
            }

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ AGREGAR ESTOS NUEVOS MÉTODOS ADMINISTRATIVOS:

    /**
     * Obtener todas las categorías para panel administrativo
     */
    public function adminIndex(): JsonResponse
    {
        try {
            $categorias = Categoria::withCount(['productos as total_productos'])
                ->with(['productos' => function($query) {
                    $query->select('id', 'nombre', 'precio', 'categoria_id', 'activo');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'categorias' => $categorias,
                'total' => $categorias->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas por categoría
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $estadisticas = Categoria::withCount(['productos as total_productos'])
                ->with(['productos' => function($query) {
                    $query->where('activo', true);
                }])
                ->get()
                ->map(function($categoria) {
                    return [
                        'id' => $categoria->id,
                        'nombre' => $categoria->nombre,
                        'total_productos' => $categoria->total_productos,
                        'productos_activos' => $categoria->productos->where('activo', true)->count(),
                        'slug' => $categoria->slug
                    ];
                });

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}