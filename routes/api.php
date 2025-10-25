<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ChecklistTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\TireController;

// Incluir rotas do ACL
require __DIR__.'/acl.php';

// Rota para verificar permissões específicas
Route::middleware('auth:api')->get('/auth/permissions/check', [App\Http\Controllers\AuthController::class, 'checkPermissions']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas públicas
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas protegidas por autenticação (com tenant automático)
Route::middleware(['auth:api', 'tenancy'])->group(function () {
    
    // Autenticação
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // === CORE: CHECKLISTS ===
    Route::apiResource('checklists', ChecklistController::class)->middleware('permission:checklists.manage');
    Route::post('/checklists/{checklist}/photos', [ChecklistController::class, 'uploadPhotos'])->middleware('permission:checklists.upload_photos');
    Route::get('/checklists/{checklist}/pdf', [ChecklistController::class, 'generatePdf'])->middleware('permission:checklists.generate_pdf');
    
    // === TEMPLATES DE CHECKLIST ===
    Route::apiResource('checklist-templates', ChecklistTemplateController::class)->middleware('permission:checklist_templates.manage');
    Route::post('/checklist-templates/{id}/duplicate', [ChecklistTemplateController::class, 'duplicate'])->middleware('permission:checklist_templates.create');
    
    // === EQUIPAMENTOS ===
    Route::apiResource('equipment', EquipmentController::class)->middleware('permission:equipment.manage');
    
    // === CONTROLE DE PNEUS ===
    Route::apiResource('tires', TireController::class)->middleware('permission:tires.manage');
    Route::post('/tires/{id}/remove', [TireController::class, 'remove'])->middleware('permission:tires.edit');
    Route::get('/tires/vehicle/{vehicleId}/current', [TireController::class, 'currentTires'])->middleware('permission:tires.view');
    Route::get('/tires/reports/wear', [TireController::class, 'wearReport'])->middleware('permission:tires.reports');
    
    // === DASHBOARD ===
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->middleware('permission:dashboard.view');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->middleware('permission:dashboard.view');
    Route::get('/dashboard/quick-actions', [DashboardController::class, 'getQuickActions'])->middleware('permission:dashboard.view');
});

// === ROTAS COM TENANT ESPECÍFICO (COMPATIBILIDADE) ===
// Padrão: /api/tenant/{account_id}/...
Route::prefix('tenant/{account_id}')->middleware(['auth:api', 'tenancy'])->group(function () {
    
    // === CORE: CHECKLISTS ===
    Route::apiResource('checklists', ChecklistController::class)->middleware('permission:checklists.manage')->names('tenant.checklists');
    Route::post('/checklists/{checklist}/photos', [ChecklistController::class, 'uploadPhotos'])->middleware('permission:checklists.upload_photos')->name('tenant.checklists.photos');
    Route::get('/checklists/{checklist}/pdf', [ChecklistController::class, 'generatePdf'])->middleware('permission:checklists.generate_pdf')->name('tenant.checklists.pdf');
    
    // === TEMPLATES DE CHECKLIST ===
    Route::apiResource('checklist-templates', ChecklistTemplateController::class)->middleware('permission:checklist_templates.manage')->names('tenant.checklist-templates');
    Route::post('/checklist-templates/{id}/duplicate', [ChecklistTemplateController::class, 'duplicate'])->middleware('permission:checklist_templates.create')->name('tenant.checklist-templates.duplicate');
    
    // === EQUIPAMENTOS ===
    Route::apiResource('equipment', EquipmentController::class)->middleware('permission:equipment.manage')->names('tenant.equipment');
    
    // === CONTROLE DE PNEUS ===
    Route::apiResource('tires', TireController::class)->middleware('permission:tires.manage')->names('tenant.tires');
    Route::post('/tires/{id}/remove', [TireController::class, 'remove'])->middleware('permission:tires.edit')->name('tenant.tires.remove');
    Route::get('/tires/vehicle/{vehicleId}/current', [TireController::class, 'currentTires'])->middleware('permission:tires.view')->name('tenant.tires.current');
    Route::get('/tires/reports/wear', [TireController::class, 'wearReport'])->middleware('permission:tires.reports')->name('tenant.tires.wear-report');
    
    // === DASHBOARD ===
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->middleware('permission:dashboard.view')->name('tenant.dashboard.stats');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->middleware('permission:dashboard.view')->name('tenant.dashboard.chart-data');
    Route::get('/dashboard/quick-actions', [DashboardController::class, 'getQuickActions'])->middleware('permission:dashboard.view')->name('tenant.dashboard.quick-actions');
});

// Rota de teste
Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando!',
        'timestamp' => now(),
    ]);
});
