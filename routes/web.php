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

    // Rotas de administração com middleware de permission
    Route::prefix('admin')->name('admin.')->middleware(['permission:access admin'])->group(function () {
        // Gestão de usuários
        Route::resource('users', App\Http\Controllers\Admin\UserController::class)->middleware(['permission:manage users']);
        Route::put('users/{user}/roles', [App\Http\Controllers\Admin\UserController::class, 'updateRoles'])
            ->name('users.roles.update')->middleware(['permission:edit users']);
        Route::get('users/{user}/permissions', [App\Http\Controllers\Admin\UserController::class, 'permissions'])
            ->name('users.permissions')->middleware(['permission:view users']);

        // Gestão de roles
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class)->middleware(['permission:manage roles']);
        Route::get('roles-permissions', [App\Http\Controllers\Admin\RoleController::class, 'permissions'])
            ->name('roles.permissions')->middleware(['permission:view roles']);
        Route::post('roles/assign-user', [App\Http\Controllers\Admin\RoleController::class, 'assignToUser'])
            ->name('roles.assign-user')->middleware(['permission:edit roles']);
        Route::delete('roles/remove-user', [App\Http\Controllers\Admin\RoleController::class, 'removeFromUser'])
            ->name('roles.remove-user')->middleware(['permission:edit roles']);

        // Gestão de permissões
        Route::resource('permissions', App\Http\Controllers\Admin\PermissionController::class)->middleware(['permission:manage permissions']);
        Route::get('permissions-api', [App\Http\Controllers\Admin\PermissionController::class, 'api'])
            ->name('permissions.api')->middleware(['permission:view permissions']);
        Route::post('permissions/bulk-assign-role', [App\Http\Controllers\Admin\PermissionController::class, 'bulkAssignToRole'])
            ->name('permissions.bulk-assign-role')->middleware(['permission:edit permissions']);
        Route::delete('permissions/bulk-remove-role', [App\Http\Controllers\Admin\PermissionController::class, 'bulkRemoveFromRole'])
            ->name('permissions.bulk-remove-role')->middleware(['permission:edit permissions']);
    });

    // Página de demonstração dos toast messages
    Route::get('toast-demo', function () {
        return Inertia::render('toast-demo');
    })->name('toast-demo');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
