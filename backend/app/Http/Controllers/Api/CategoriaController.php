<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

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
        // Para futuro panel admin
        return response()->json(['message' => 'Método no implementado'], 501);
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
}