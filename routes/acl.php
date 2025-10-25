<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ACL Routes
|--------------------------------------------------------------------------
|
| Aqui estão todas as rotas relacionadas ao sistema de ACL
| (Access Control List) incluindo permissões, roles, menus e usuários.
|
*/

Route::middleware(['auth:api'])->group(function () {
    
    // Rotas para Menus
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('/all', [MenuController::class, 'all'])->middleware('permission:admin.menus');
        Route::post('/', [MenuController::class, 'store'])->middleware('permission:admin.menus');
        Route::put('/{menu}', [MenuController::class, 'update'])->middleware('permission:admin.menus');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->middleware('permission:admin.menus');
    });

    // Rotas para Roles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:admin.roles');
        Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:admin.roles');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:admin.roles');
    });

    // Rotas para Permissões
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::post('/', [PermissionController::class, 'store'])->middleware('permission:admin.permissions');
        Route::put('/{permission}', [PermissionController::class, 'update'])->middleware('permission:admin.permissions');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:admin.permissions');
    });

    // Rotas para Usuários
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:admin.users');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:admin.users');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:admin.users');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:admin.users');
        Route::get('/{user}/permissions', [UserController::class, 'permissions']);
    });

    // Rotas para Auditoria
    Route::prefix('audit')->group(function () {
        Route::get('/logs', [AuditController::class, 'index'])->middleware('permission:admin.audit');
        Route::get('/logs/{auditLog}', [AuditController::class, 'show'])->middleware('permission:admin.audit');
        Route::get('/statistics', [AuditController::class, 'statistics'])->middleware('permission:admin.audit');
        Route::get('/models', [AuditController::class, 'models'])->middleware('permission:admin.audit');
        Route::get('/export', [AuditController::class, 'export'])->middleware('permission:admin.audit');
    });
});
