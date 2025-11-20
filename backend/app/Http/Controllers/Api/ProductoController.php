<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

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
        // Para futuro panel admin
        return response()->json(['message' => 'Método no implementado'], 501);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $producto = Producto::with('categoria')->find($id);
        
        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($producto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Para futuro panel admin
        return response()->json(['message' => 'Método no implementado'], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Para futuro panel admin
        return response()->json(['message' => 'Método no implementado'], 501);
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
}