<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    /**
     * Obtener items del carrito del usuario autenticado
     */
    public function index(Request $request)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación manualmente
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado'
                ], 401);
            }

            $carritoItems = Carrito::with('producto.categoria')
                                ->where('usuario_id', $user->id)
                                ->get();

            $total = $carritoItems->sum(function ($item) {
                return $item->cantidad * $item->precio_unitario;
            });

            return response()->json([
                'success' => true,
                'items' => $carritoItems,
                'total' => $total,
                'count' => $carritoItems->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar producto al carrito
     */
    public function store(Request $request)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'cantidad' => 'required|integer|min:1'
            ]);

            $producto = Producto::findOrFail($request->producto_id);

            // Verificar si ya existe en el carrito
            $carritoItem = Carrito::where('usuario_id', $user->id)
                                ->where('producto_id', $request->producto_id)
                                ->first();

            if ($carritoItem) {
                $carritoItem->cantidad += $request->cantidad;
                $carritoItem->save();
            } else {
                $carritoItem = Carrito::create([
                    'usuario_id' => $user->id,
                    'producto_id' => $request->producto_id,
                    'cantidad' => $request->cantidad,
                    'precio_unitario' => $producto->precio
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'item' => $carritoItem->load('producto.categoria')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cantidad de un item
     */
    public function update(Request $request, $id)
    {
        // Forzar JSON
        $request->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $request->validate([
                'cantidad' => 'required|integer|min:1'
            ]);

            $carritoItem = Carrito::where('usuario_id', $user->id)
                                ->where('id', $id)
                                ->firstOrFail();

            $carritoItem->cantidad = $request->cantidad;
            $carritoItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'item' => $carritoItem->load('producto.categoria')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar item del carrito
     */
    public function destroy($id)
    {
        // Forzar JSON
        request()->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $carritoItem = Carrito::where('usuario_id', $user->id)
                                ->where('id', $id)
                                ->firstOrFail();

            $carritoItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vaciar carrito completo
     */
    public function vaciar()
    {
        // Forzar JSON
        request()->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            Carrito::where('usuario_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Carrito vaciado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen del carrito (cantidad de items)
     */
    public function resumen()
    {
        // Forzar JSON
        request()->headers->set('Accept', 'application/json');

        try {
            // Verificar autenticación
            $user = auth()->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $count = Carrito::where('usuario_id', $user->id)->count();
            $items = Carrito::with('producto')->where('usuario_id', $user->id)->get();
            
            $total = $items->sum(function ($item) {
                return $item->cantidad * $item->precio_unitario;
            });

            return response()->json([
                'success' => true,
                'count' => $count,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}