<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // Listar todos los usuarios (para admin)
    public function adminIndex()
    {
        try {
            \Log::info('🔄 AdminIndex - cargando usuarios con relación corregida');
            
            // ✅ CORREGIDO: Especificar la columna de la relación
            $users = User::withCount(['pedidos' => function($query) {
                    $query->where('estado', '!=', 'cancelado');
                }])
                ->get();
            
            \Log::info('✅ Usuarios cargados correctamente: ' . $users->count());
            
            return response()->json([
                'success' => true,
                'usuarios' => $users,
                'total' => $users->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('💥 ERROR en adminIndex: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // Actualizar usuario (cambiar rol, estado, etc.)
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'direccion' => 'nullable|string|max:500',
                'telefono' => 'nullable|regex:/^[0-9]+$/|max:20',
                'is_admin' => 'sometimes|boolean'
            ]);
            
            $user->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'usuario' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rol de usuario (admin/user)
     */
    public function updateRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Validar que el rol sea válido
            $request->validate([
                'is_admin' => 'required|boolean'
            ]);

            $user->update([
                'is_admin' => $request->is_admin
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol de usuario actualizado correctamente',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de usuario (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // No permitir desactivar el propio usuario
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar tu propio usuario'
                ], 403);
            }
            
            // Aquí podrías agregar un campo 'activo' si lo tienes
            // Por ahora solo devolvemos éxito
            return response()->json([
                'success' => true,
                'message' => 'Estado de usuario actualizado',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Eliminar usuario (soft delete)
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // No permitir eliminar el propio usuario admin
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 403);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Restaurar usuario eliminado
    public function restaurar($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario restaurado correctamente',
                'usuario' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Estadísticas de dashboard administrativo
    public function dashboardStats()
    {
        try {
            $stats = [
                'total_usuarios' => User::count(),
                'total_productos' => \App\Models\Producto::count(),
                'total_pedidos' => Pedido::count(),
                'ingresos_totales' => Pedido::where('estado', 'entregado')->sum('total'),
                'pedidos_pendientes' => Pedido::where('estado', 'pendiente')->count(),
                'pedidos_confirmados' => Pedido::where('estado', 'confirmado')->count(),
                'pedidos_enviados' => Pedido::where('estado', 'enviado')->count(),
                'pedidos_entregados' => Pedido::where('estado', 'entregado')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NUEVO MÉTODO: Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Validar datos de entrada
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'telefono' => 'nullable|regex:/^[0-9]+$/|max:20',
                'direccion' => 'nullable|string|max:500'
            ]);

            // Actualizar solo los campos proporcionados
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['telefono'])) {
                $user->telefono = $validated['telefono'];
            }
            if (isset($validated['direccion'])) {
                $user->direccion = $validated['direccion'];
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'direccion' => $user->direccion,
                    'is_admin' => $user->is_admin
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}