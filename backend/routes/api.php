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
use App\Http\Controllers\Api\AdvancedReportsController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProveedorController;

// ==================== RUTAS PÚBLICAS ====================

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

// ✅ CORREGIDO: RUTAS DE RECUPERACIÓN DE CONTRASEÑA FUERA DE AUTENTICACIÓN
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Ruta simple de prueba
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});

// ==================== RUTAS PROTEGIDAS (USUARIOS AUTENTICADOS) ====================

Route::middleware('auth:api')->group(function () {

    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'user']);

    // Perfil de usuario
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index']);
    Route::post('/carrito/agregar', [CarritoController::class, 'store']);
    Route::put('/carrito/actualizar/{id}', [CarritoController::class, 'update']);
    Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'destroy']);
    Route::delete('/carrito/vaciar', [CarritoController::class, 'vaciar']);
    Route::get('/carrito/resumen', [CarritoController::class, 'resumen']);

    // ✅ NUEVAS RUTAS DE PAGOS REALES
    Route::get('/metodos-pago', [PedidoController::class, 'metodosPago']);
    Route::post('/pedidos/checkout-con-pago', [PedidoController::class, 'checkoutConPago']);

    // Pedidos (rutas existentes + nuevas)
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
    Route::put('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']);

    // ✅ RUTA PARA CAMBIAR ESTADO DE PEDIDOS
    Route::post('/pedidos/{id}/estado', [PedidoController::class, 'updateStatus']);

    // Favoritos
    Route::get('/favoritos', [FavoritoController::class, 'index']);
    Route::post('/favoritos/agregar/{producto_id}', [FavoritoController::class, 'store']);
    Route::delete('/favoritos/eliminar/{producto_id}', [FavoritoController::class, 'destroy']);
    Route::get('/favoritos/verificar/{producto_id}', [FavoritoController::class, 'check']);

    // Reseñas
    Route::get('/productos/{id}/resenas', [ResenaController::class, 'index']);
    Route::get('/mis-resenas', [ResenaController::class, 'misResenas']);
    Route::post('/resenas', [ResenaController::class, 'store']);
    Route::put('/resenas/{id}', [ResenaController::class, 'update']);
    Route::delete('/resenas/{id}', [ResenaController::class, 'destroy']);

    // Chat para usuarios normales
    Route::prefix('chat')->group(function () {
        // Conversaciones
        Route::get('/conversations', [ConversationController::class, 'index']);
        Route::post('/conversations', [ConversationController::class, 'store']);
        Route::get('/conversations/{id}', [ConversationController::class, 'show']);

        // Mensajes
        Route::post('/messages', [MessageController::class, 'store']);
        Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
    });

    // ============================================
    // ✅ RUTAS DE INVENTARIO AVANZADO
    // ============================================

    // Inventario
    Route::get('/inventory/stock-bajo', [InventoryController::class, 'stockBajo']);
    Route::get('/inventory/estadisticas', [InventoryController::class, 'estadisticas']);
    Route::post('/inventory/registrar-movimiento', [InventoryController::class, 'registrarMovimiento']);
    Route::get('/inventory/historial-movimientos', [InventoryController::class, 'historialMovimientos']);

    // Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::get('/proveedores/estadisticas', [ProveedorController::class, 'estadisticas']);
    Route::post('/proveedores', [ProveedorController::class, 'store']);
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);
});

// ==================== RUTAS ADMINISTRATIVAS (SOLO PARA ADMINS) ====================

Route::middleware(['auth:api', \App\Http\Middleware\AdminMiddleware::class])->group(function () {

    // Dashboard
    Route::get('/admin/dashboard-stats', [UserController::class, 'dashboardStats']);

    // Gestión de pedidos
    Route::get('/admin/pedidos', [AdminController::class, 'getAllOrders']);
    Route::put('/admin/pedidos/{id}/estado', [AdminController::class, 'updateOrderStatus']);

    // Gestión de usuarios
    Route::prefix('admin')->group(function () {
        Route::get('/usuarios', [UserController::class, 'adminIndex']);
        Route::put('/usuarios/{id}', [UserController::class, 'update']);
        Route::put('/usuarios/{id}/role', [UserController::class, 'updateRole']);
        Route::put('/usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
        Route::put('/usuarios/{id}/restaurar', [UserController::class, 'restaurar']);
    });

    // Gestión de productos
    Route::get('/admin/productos', [ProductoController::class, 'adminIndex']);
    Route::post('/admin/productos', [ProductoController::class, 'store']);
    Route::put('/admin/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/admin/productos/{id}', [ProductoController::class, 'destroy']);
    Route::put('/admin/productos/{id}/restaurar', [ProductoController::class, 'restaurar']);
    Route::delete('/admin/productos/{id}/permanent', [ProductoController::class, 'eliminarPermanente']);

    // Gestión de categorías
    Route::get('/admin/categorias', [CategoriaController::class, 'adminIndex']);
    Route::get('/admin/categorias/estadisticas', [CategoriaController::class, 'estadisticas']);
    Route::post('/admin/categorias', [CategoriaController::class, 'store']);
    Route::put('/admin/categorias/{id}', [CategoriaController::class, 'update']);
    Route::delete('/admin/categorias/{id}', [CategoriaController::class, 'destroy']);

    // Reportes
    Route::prefix('admin/reports')->group(function () {
        Route::get('/metricas-generales', [ReportController::class, 'metricasGenerales']);

        // ✅ RUTAS PARA REPORTES AVANZADOS
        Route::get('/ventas-periodo', [AdvancedReportsController::class, 'ventasPorPeriodo']);
        Route::get('/productos-mas-vendidos', [AdvancedReportsController::class, 'productosMasVendidos']);
        Route::get('/metricas-usuarios', [AdvancedReportsController::class, 'metricasUsuarios']);
        Route::get('/estadisticas-categorias', [AdvancedReportsController::class, 'estadisticasCategorias']);
    });

    // Chat para administradores
    Route::prefix('admin/chat')->group(function () {
        Route::get('/conversations', [AdminChatController::class, 'index']);
        Route::get('/conversations/unread', [AdminChatController::class, 'unreadConversations']);
        Route::put('/conversations/{id}/close', [AdminChatController::class, 'closeConversation']);
        Route::put('/conversations/{id}/reopen', [AdminChatController::class, 'reopenConversation']);
    });

    // ✅ RUTAS ADMINISTRATIVAS DE INVENTARIO
    Route::prefix('admin/inventory')->group(function () {
        // Aquí puedes agregar rutas específicas para administradores si es necesario
    });
});