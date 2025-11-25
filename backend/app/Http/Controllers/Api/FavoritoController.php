<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    // Obtener todos los favoritos del usuario
    public function index()
    {
        try {
            $user = Auth::user();
            $favoritos = Favorito::with(['producto.categoria'])
                                ->where('usuario_id', $user->id)
                                ->get();

            return response()->json([
                'success' => true,
                'favoritos' => $favoritos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener favoritos'
            ], 500);
        }
    }

    // Agregar producto a favoritos
    public function store(Request $request, $producto_id)
    {
        try {
            $user = Auth::user();
            
            // Verificar si el producto existe
            $producto = Producto::where('id', $producto_id)
                               ->where('activo', true)
                               ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Verificar si ya está en favoritos
            $favoritoExistente = Favorito::where('usuario_id', $user->id)
                                        ->where('producto_id', $producto_id)
                                        ->first();

            if ($favoritoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto ya está en tus favoritos'
                ], 409);
            }

            // Crear favorito
            $favorito = Favorito::create([
                'usuario_id' => $user->id,
                'producto_id' => $producto_id
            ]);

            $favorito->load('producto.categoria');

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado a favoritos',
                'favorito' => $favorito
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar a favoritos'
            ], 500);
        }
    }

    // Eliminar producto de favoritos
    public function destroy($producto_id)
    {
        try {
            $user = Auth::user();

            $favorito = Favorito::where('usuario_id', $user->id)
                               ->where('producto_id', $producto_id)
                               ->first();

            if (!$favorito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado en favoritos'
                ], 404);
            }

            $favorito->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado de favoritos'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar de favoritos'
            ], 500);
        }
    }

    // Verificar si un producto está en favoritos
    public function check($producto_id)
    {
        try {
            $user = Auth::user();

            $esFavorito = Favorito::where('usuario_id', $user->id)
                                 ->where('producto_id', $producto_id)
                                 ->exists();

            return response()->json([
                'success' => true,
                'es_favorito' => $esFavorito
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar favorito'
            ], 500);
        }
    }
}