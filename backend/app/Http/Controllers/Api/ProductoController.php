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
        \Log::info('Datos recibidos para crear producto:', $request->all());

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'precio_original' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'imagen_url' => 'nullable|url',
            'badge' => 'nullable|in:mas-vendido,oferta,nuevo,esencial',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'costo' => 'nullable|numeric|min:0'
        ]);

        \Log::info('Validación pasada, creando producto...');

        $data = $request->all();

        // ✅ Asegurar que los campos numéricos tengan valores por defecto
        $data['stock'] = $data['stock'] ?? 0;
        $data['stock_minimo'] = $data['stock_minimo'] ?? 10;
        $data['stock_maximo'] = $data['stock_maximo'] ?? 100;
        $data['activo'] = $data['activo'] ?? true;

        // Manejar imagen
        if ($request->hasFile('imagen')) {
            $imagePath = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = '/storage/' . $imagePath;
            \Log::info('Imagen subida: ' . $data['imagen']);
        }
        elseif ($request->has('imagen_url') && !empty($request->imagen_url)) {
            $data['imagen'] = $request->imagen_url;
            \Log::info('URL de imagen usada: ' . $data['imagen']);
        }

        \Log::info('Datos finales para crear producto:', $data);

        $producto = Producto::create($data);

        \Log::info('Producto creado exitosamente: ' . $producto->id);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data' => $producto
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Error CRÍTICO al crear producto: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Error al crear producto',
            'error' => $e->getMessage(),
            'debug' => 'Verificar logs para más detalles'
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
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'precio_original' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'imagen_url' => 'nullable|url',
            'badge' => 'nullable|in:mas-vendido,oferta,nuevo,esencial',
            'activo' => 'boolean',
            'stock_minimo' => 'nullable|integer|min:0',
            'stock_maximo' => 'nullable|integer|min:0',
            'costo' => 'nullable|numeric|min:0'
        ]);

        $data = $request->all();

        // ✅ Manejar subida de archivo de imagen (edición)
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && strpos($producto->imagen, '/storage/') === 0) {
                $oldImagePath = str_replace('/storage/', '', $producto->imagen);
                Storage::disk('public')->delete($oldImagePath);
            }
            
            $imagePath = $request->file('imagen')->store('productos', 'public');
            $data['imagen'] = '/storage/' . $imagePath;
        }
        // ✅ Manejar URL de imagen (edición)
        elseif ($request->has('imagen_url') && !empty($request->imagen_url)) {
            $data['imagen'] = $request->imagen_url;
        }
        // ✅ Si no se proporciona nueva imagen, mantener la existente
        else {
            $data['imagen'] = $producto->imagen;
        }

        $producto->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'data' => $producto
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar producto',
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