<?php

use App\Http\Controllers\AuthController;
use App\Livewire\CartCheckout;
use App\Livewire\HomePage;
use App\Livewire\MenuCatalog;
use App\Livewire\OrdersPage;
use App\Livewire\ProfilePage;
use App\Livewire\PromosPage;
use Illuminate\Support\Facades\Route;

// Pantallas públicas SPA (sin login requerido para explorar)
Route::get('/', HomePage::class)->name('home');
Route::get('/menu', MenuCatalog::class)->name('menu');
Route::get('/promos', PromosPage::class)->name('promos');
Route::get('/cart', CartCheckout::class)->name('cart');

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

// Pantallas protegidas (requieren iniciar sesión)
Route::middleware('auth')->group(function () {
    Route::get('/mis-pedidos', OrdersPage::class)->name('orders.index');
    Route::get('/profile', ProfilePage::class)->name('profile');
});
