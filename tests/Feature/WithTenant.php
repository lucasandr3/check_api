<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Stancl\Tenancy\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait WithTenant
{
    protected $tenant;

    protected function setUpTenant(): void
    {
        // Executar migrações para criar a tabela tenants
        $this->artisan('migrate');
        
        $this->tenant = Tenant::factory()->create([
            'name' => 'Test Tenant',
            'domain' => 'fixcar_api.test',
            'database' => 'tenant_test'
        ]);
        
        app(Tenancy::class)->initialize($this->tenant);
    }

    protected function tearDownTenant(): void
    {
        if ($this->tenant) {
            app(Tenancy::class)->end();
        }
    }
}
