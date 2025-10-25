<?php

// ServiceController removido - sistema agora focado em gestão de frota
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Exemplo de Uso dos Middlewares de ACL
|--------------------------------------------------------------------------
|
| Este arquivo demonstra como usar os middlewares de verificação de permissões
| para proteger rotas baseadas no sistema de ACL.
|
*/

Route::middleware(['auth:api'])->group(function () {
    
    // ========================================
    // SERVIÇOS - Com verificação de permissões
    // ========================================
    
    // Visualizar serviços - todos os usuários autenticados
    Route::get('/equipment', [EquipmentController::class, 'index'])
        ->middleware('permission:services.view');
    
    // Criar serviço - usuários com permissão de criação
    Route::post('/equipment', [EquipmentController::class, 'store'])
        ->middleware('permission:services.create');
    
    // Editar serviço - usuários com permissão de edição
    Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])
        ->middleware('permission:services.edit');
    
    // Excluir serviço - usuários com permissão de exclusão
    Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])
        ->middleware('permission:services.delete');
    
    // ========================================
    // CHECKLISTS - Com verificação de permissões
    // ========================================
    
    Route::get('/checklists', [ChecklistController::class, 'index'])
        ->middleware('permission:checklists.view');
    
    Route::post('/checklists', [ChecklistController::class, 'store'])
        ->middleware('permission:checklists.create');
    
    Route::put('/checklists/{checklist}', [ChecklistController::class, 'update'])
        ->middleware('permission:checklists.edit');
    
    Route::delete('/checklists/{checklist}', [ChecklistController::class, 'destroy'])
        ->middleware('permission:checklists.delete');
    
    // ========================================
    // CLIENTES - Com verificação de permissões
    // ========================================
    
    Route::get('/clients', [ClientController::class, 'index'])
        ->middleware('permission:clients.view');
    
    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('permission:clients.create');
    
    Route::put('/clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:clients.edit');
    
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:clients.delete');
    
    // ========================================
    // VEÍCULOS - Com verificação de permissões
    // ========================================
    
    Route::get('/vehicles', [VehicleController::class, 'index'])
        ->middleware('permission:vehicles.view');
    
    Route::post('/vehicles', [VehicleController::class, 'store'])
        ->middleware('permission:vehicles.create');
    
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])
        ->middleware('permission:vehicles.edit');
    
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])
        ->middleware('permission:vehicles.delete');
    
    // ========================================
    // EXEMPLOS DE USO AVANÇADO
    // ========================================
    
    // Rota que requer múltiplas permissões (todas)
    Route::get('/reports/fuel', [FuelController::class, 'stats'])
        ->middleware('permission:services.view,services.edit');
    
    // Rota que requer qualquer uma das permissões
    Route::get('/dashboard', [DashboardController::class, 'getStats'])
        ->middleware('any.permission:services.view,checklists.view,clients.view');
    
    // Rota administrativa que requer permissão específica
    Route::get('/admin/statistics', [DashboardController::class, 'getChartData'])
        ->middleware('permission:admin.users');
    
    // ========================================
    // GRUPOS DE ROTAS POR PERMISSÃO
    // ========================================
    
    // Rotas para usuários com permissão de administração
    Route::middleware(['permission:admin.users'])->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
    
    // Rotas para usuários com permissão de gerenciamento
    Route::middleware(['permission:services.edit,checklists.edit'])->prefix('management')->group(function () {
        Route::get('/pending-maintenance', [MaintenanceController::class, 'pending']);
        Route::get('/maintenance-queue', [MaintenanceController::class, 'queue']);
        Route::post('/approve-maintenance/{maintenance}', [MaintenanceController::class, 'approve']);
    });
    
    // ========================================
    // EXEMPLOS DE VERIFICAÇÃO NO CONTROLLER
    // ========================================
    
    // No controller, você também pode verificar permissões:
    /*
    public function someAction(Request $request)
    {
        $user = $request->user();
        
        // Verificar permissão específica
        if (!$user->hasPermission('services.edit')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Permissão insuficiente.'
            ], 403);
        }
        
        // Verificar múltiplas permissões
        if (!$user->hasAnyPermission(['services.edit', 'services.delete'])) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Permissão insuficiente.'
            ], 403);
        }
        
        // Lógica da ação...
    }
    */
    
    // ========================================
    // EXEMPLOS DE USO NO FRONTEND
    // ========================================
    
    /*
    // JavaScript/TypeScript
    const userPermissions = user.permissions; // Array de permissões
    
    // Verificar se usuário pode criar serviços
    if (userPermissions.includes('services.create')) {
        showCreateServiceButton();
    }
    
    // Verificar se usuário pode administrar usuários
    if (userPermissions.includes('admin.users')) {
        showUsersManagementMenu();
    }
    
    // Verificar múltiplas permissões
    if (userPermissions.some(p => ['services.edit', 'services.delete'].includes(p))) {
        showServiceActions();
    }
    */
});

/*
|--------------------------------------------------------------------------
| NOTAS IMPORTANTES
|--------------------------------------------------------------------------
|
| 1. SEMPRE use middlewares para proteger rotas no backend
| 2. NUNCA confie apenas na verificação no frontend
| 3. Use 'permission:permissao' para verificar permissão específica
| 4. Use 'any.permission:permissao1,permissao2' para verificar qualquer uma
| 5. Combine middlewares conforme necessário
| 6. Documente todas as permissões utilizadas
| 7. Teste as permissões com diferentes roles de usuário
|
*/
