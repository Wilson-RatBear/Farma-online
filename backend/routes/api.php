<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\PromocionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UserController; 
use App\Http\Controllers\Api\ReportController; 
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\ResenaController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\AdminChatController;

// Rutas de Categorías
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/{id}', [CategoriaController::class, 'show']);

// Rutas de Productos
Route::get('/productos', [ProductoController::class, 'index']);
Route::get('/productos/{id}', [ProductoController::class, 'show']);
Route::get('/productos/buscar/{query}', [ProductoController::class, 'buscar']);

// Rutas de Promociones
Route::get('/promociones', [PromocionController::class, 'index']);
Route::get('/promociones/activas', [PromocionController::class, 'activas']);
Route::get('/promociones/{id}', [PromocionController::class, 'show']);

// Rutas de Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/user', [AuthController::class, 'user']);

// NUEVA RUTA: Actualizar perfil de usuario
Route::put('/user/profile', [UserController::class, 'updateProfile'])->middleware('auth:api');

// Rutas de Carrito (protección en controlador)
Route::get('/carrito', [CarritoController::class, 'index']);
Route::post('/carrito/agregar', [CarritoController::class, 'store']);
Route::put('/carrito/actualizar/{id}', [CarritoController::class, 'update']);
Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'destroy']);
Route::delete('/carrito/vaciar', [CarritoController::class, 'vaciar']);
Route::get('/carrito/resumen', [CarritoController::class, 'resumen']);

// Rutas de Pedidos (protección en controlador)
Route::get('/pedidos', [PedidoController::class, 'index']);
Route::post('/pedidos/checkout', [PedidoController::class, 'store']);
Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
Route::put('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']);

// ✅ RUTAS DE FAVORITOS - MOVIDAS FUERA DEL GRUPO ADMIN
Route::middleware('auth:api')->group(function () {
    Route::get('/favoritos', [FavoritoController::class, 'index']);
    Route::post('/favoritos/agregar/{producto_id}', [FavoritoController::class, 'store']);
    Route::delete('/favoritos/eliminar/{producto_id}', [FavoritoController::class, 'destroy']);
    Route::get('/favoritos/verificar/{producto_id}', [FavoritoController::class, 'check']);
});

// ✅ RUTAS DE RESEÑAS - MOVIDAS FUERA DEL GRUPO ADMIN
Route::middleware('auth:api')->group(function () {
    Route::get('/productos/{id}/resenas', [ResenaController::class, 'index']);
    Route::get('/mis-resenas', [ResenaController::class, 'misResenas']);
    Route::post('/resenas', [ResenaController::class, 'store']);
    Route::put('/resenas/{id}', [ResenaController::class, 'update']);
    Route::delete('/resenas/{id}', [ResenaController::class, 'destroy']);
});

// Ruta simple de prueba
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});

// Rutas administrativas (SOLO PARA ADMINS)
Route::middleware(['auth:api', 'admin'])->group(function () {
    
    // Dashboard - Usar UserController para estadísticas
    Route::get('/admin/dashboard-stats', [UserController::class, 'dashboardStats']);
    
    // Gestión de pedidos
    Route::get('/admin/pedidos', [AdminController::class, 'getAllOrders']);
    Route::put('/admin/pedidos/{id}/estado', [AdminController::class, 'updateOrderStatus']);

    // 🔥 RUTAS UNIFICADAS PARA GESTIÓN DE USUARIOS
    Route::prefix('admin')->group(function () {
        Route::get('/usuarios', [UserController::class, 'adminIndex']);
        Route::put('/usuarios/{id}', [UserController::class, 'update']);
        Route::put('/usuarios/{id}/role', [UserController::class, 'updateRole']); 
        Route::put('/usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus']); 
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
        Route::put('/usuarios/{id}/restaurar', [UserController::class, 'restaurar']);
    });
    
    // 🔥 RUTAS PARA GESTIÓN DE PRODUCTOS
    Route::get('/admin/productos', [ProductoController::class, 'adminIndex']);
    Route::post('/admin/productos', [ProductoController::class, 'store']);
    Route::put('/admin/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/admin/productos/{id}', [ProductoController::class, 'destroy']);
    Route::put('/admin/productos/{id}/restaurar', [ProductoController::class, 'restaurar']);
    Route::delete('/admin/productos/{id}/permanent', [ProductoController::class, 'eliminarPermanente']);

    // 🔥 RUTAS PARA GESTIÓN DE CATEGORÍAS (NUEVAS)
    Route::get('/admin/categorias', [CategoriaController::class, 'adminIndex']);
    Route::get('/admin/categorias/estadisticas', [CategoriaController::class, 'estadisticas']);
    Route::post('/admin/categorias', [CategoriaController::class, 'store']);
    Route::put('/admin/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/admin/categorias/{id}', [CategoriaController::class, 'destroy']);

    // Rutas de Reportes
    Route::prefix('admin/reports')->group(function () {
        Route::get('/metricas-generales', [ReportController::class, 'metricasGenerales']);
    });
    // ==================== RUTAS DEL CHAT ====================

// Rutas para usuarios normales
Route::middleware('auth:api')->group(function () {
    // Conversaciones
    Route::get('/chat/conversations', [ConversationController::class, 'index']);
    Route::post('/chat/conversations', [ConversationController::class, 'store']);
    Route::get('/chat/conversations/{id}', [ConversationController::class, 'show']);
    
    // Mensajes
    Route::post('/chat/messages', [MessageController::class, 'store']);
    Route::get('/chat/conversations/{id}/messages', [MessageController::class, 'getMessages']);
});

// Rutas para administradores
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('/chat/conversations', [AdminChatController::class, 'index']);
    Route::get('/chat/conversations/unread', [AdminChatController::class, 'unreadConversations']);
    Route::put('/chat/conversations/{id}/close', [AdminChatController::class, 'closeConversation']);
    Route::put('/chat/conversations/{id}/reopen', [AdminChatController::class, 'reopenConversation']);
});
});