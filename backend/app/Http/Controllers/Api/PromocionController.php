<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $promociones = Promocion::activas()->get();
        return response()->json($promociones);
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
        $promocion = Promocion::find($id);
        
        if (!$promocion) {
            return response()->json(['message' => 'Promoción no encontrada'], 404);
        }

        return response()->json($promocion);
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
     * Promociones activas
     */
    public function activas()
    {
        $promociones = Promocion::activas()->get();
        return response()->json($promociones);
    }
}