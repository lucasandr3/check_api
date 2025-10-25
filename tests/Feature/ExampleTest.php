<?php

use Tests\Feature\WithTenant;

test('the application returns a successful response', function () {
    // Executar migrações para criar a tabela tenants
    $this->artisan('migrate');
    
    // Configurar tenant de teste
    $tenant = \App\Models\Tenant::factory()->create([
        'name' => 'Test Tenant',
        'domain' => 'fixcar_api.test',
        'database' => 'tenant_test'
    ]);
    
    app(\Stancl\Tenancy\Tenancy::class)->initialize($tenant);
    
    $response = $this->get('/');

    $response->assertStatus(200);
    
    // Limpar tenant
    app(\Stancl\Tenancy\Tenancy::class)->end();
});
