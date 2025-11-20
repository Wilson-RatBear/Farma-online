<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\PromocionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\PedidoController;

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

// Ruta simple de prueba
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});