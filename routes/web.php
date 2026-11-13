<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Livewire\StoreCatalog;
use Illuminate\Support\Facades\Route;

// Catálogo público (estilo Mi Mandadito + Postrecito de Jaan)
Route::get('/', StoreCatalog::class)->name('home');

// Autenticación para clientes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Pedidos del cliente (requiere iniciar sesión)
Route::get('/mis-pedidos', [OrderController::class, 'index'])
    ->middleware('auth')
    ->name('orders.index');
