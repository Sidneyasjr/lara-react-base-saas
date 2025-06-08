<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Rotas do CRUD de usuários
    Route::resource('users', UserController::class);

    // Página de demonstração dos toast messages
    Route::get('toast-demo', function () {
        return Inertia::render('toast-demo');
    })->name('toast-demo');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
