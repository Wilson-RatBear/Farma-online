<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productos = Producto::with('categoria')
                            ->where('activo', true)
                            ->get();
        return response()->json($productos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'precio_original' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'categoria_id' => 'required|exists:categorias,id',
                'imagen' => 'nullable|string',
                'badge' => 'nullable|string|max:50',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto = Producto::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'precio_original' => $request->precio_original ?? $request->precio,
                'stock' => $request->stock,
                'categoria_id' => $request->categoria_id,
                'imagen' => $request->imagen,
                'badge' => $request->badge,
                'activo' => $request->activo ?? true
            ]);

            $producto->load('categoria');

            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'producto' => $producto
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->find($id);
        
        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json($producto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::find($id);
            
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'sometimes|required|string',
                'precio' => 'sometimes|required|numeric|min:0',
                'precio_original' => 'nullable|numeric|min:0',
                'stock' => 'sometimes|required|integer|min:0',
                'categoria_id' => 'sometimes|required|exists:categorias,id',
                'imagen' => 'nullable|string',
                'badge' => 'nullable|string|max:50',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto->update($request->all());
            $producto->load('categoria');

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'producto' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $producto = Producto::find($id);
            
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // En lugar de eliminar, desactivamos el producto
            $producto->update(['activo' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Producto desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Productos por categoría
     */
    public function porCategoria($categoriaId)
    {
        $productos = Producto::with('categoria')
                            ->where('categoria_id', $categoriaId)
                            ->where('activo', true)
                            ->get();
        
        return response()->json($productos);
    }

    /**
     * Búsqueda de productos
     */
    public function buscar(Request $request)
    {
        $query = $request->get('q');
        
        $productos = Producto::with('categoria')
                            ->where('activo', true)
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->get();
        
        return response()->json($productos);
    }

    /**
     * ADMIN: Lista todos los productos (incluyendo inactivos)
     */
    public function adminIndex()
    {
        try {
            $productos = Producto::with('categoria')
                                ->orderBy('created_at', 'desc')
                                ->get();
            
            return response()->json([
                'success' => true,
                'productos' => $productos,
                'total' => $productos->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Restaurar producto desactivado
     */
    public function restaurar($id)
    {
        try {
            $producto = Producto::find($id);
            
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $producto->update(['activo' => true]);
            $producto->load('categoria');

            return response()->json([
                'success' => true,
                'message' => 'Producto restaurado exitosamente',
                'producto' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Eliminación permanente (OPCIONAL - usar con cuidado)
     */
    public function eliminarPermanente($id)
    {
        try {
            $producto = Producto::find($id);
            
            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $producto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado permanentemente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}