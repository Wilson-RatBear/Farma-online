<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resena;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResenaController extends Controller
{
    // Obtener reseñas de un producto
    public function index($producto_id)
    {
        try {
            $resenas = Resena::with(['usuario'])
                            ->where('producto_id', $producto_id)
                            ->activas()
                            ->orderBy('created_at', 'desc')
                            ->get();

            // Calcular promedio de rating
            $promedio = Resena::where('producto_id', $producto_id)
                             ->activas()
                             ->avg('rating');

            $totalResenas = $resenas->count();

            return response()->json([
                'success' => true,
                'resenas' => $resenas,
                'estadisticas' => [
                    'promedio_rating' => round($promedio, 1),
                    'total_resenas' => $totalResenas,
                    'distribucion_ratings' => $this->getDistribucionRatings($producto_id)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reseñas'
            ], 500);
        }
    }

    // Obtener reseñas del usuario actual
    public function misResenas()
    {
        try {
            $user = Auth::user();
            
            $resenas = Resena::with(['producto.categoria'])
                            ->where('usuario_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();

            return response()->json([
                'success' => true,
                'resenas' => $resenas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tus reseñas'
            ], 500);
        }
    }

    // Crear nueva reseña
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'producto_id' => 'required|exists:productos,id',
                'rating' => 'required|integer|between:1,5',
                'comentario' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si ya existe una reseña para este producto
            $resenaExistente = Resena::where('usuario_id', $user->id)
                                    ->where('producto_id', $request->producto_id)
                                    ->first();

            if ($resenaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has realizado una reseña para este producto'
                ], 409);
            }

            // Crear reseña
            $resena = Resena::create([
                'usuario_id' => $user->id,
                'producto_id' => $request->producto_id,
                'rating' => $request->rating,
                'comentario' => $request->comentario
            ]);

            $resena->load(['usuario', 'producto']);

            return response()->json([
                'success' => true,
                'message' => 'Reseña creada exitosamente',
                'resena' => $resena
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear reseña'
            ], 500);
        }
    }

    // Actualizar reseña
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $resena = Resena::where('id', $id)
                           ->where('usuario_id', $user->id)
                           ->first();

            if (!$resena) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reseña no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'sometimes|integer|between:1,5',
                'comentario' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $resena->update($request->only(['rating', 'comentario']));
            $resena->load(['usuario', 'producto']);

            return response()->json([
                'success' => true,
                'message' => 'Reseña actualizada exitosamente',
                'resena' => $resena
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar reseña'
            ], 500);
        }
    }

    // Eliminar reseña
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            $resena = Resena::where('id', $id)
                           ->where('usuario_id', $user->id)
                           ->first();

            if (!$resena) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reseña no encontrada'
                ], 404);
            }

            $resena->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar reseña'
            ], 500);
        }
    }

    // Función auxiliar para distribución de ratings
    private function getDistribucionRatings($producto_id)
    {
        $distribucion = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribucion[$i] = Resena::where('producto_id', $producto_id)
                                     ->activas()
                                     ->where('rating', $i)
                                     ->count();
        }
        return $distribucion;
    }
}