<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Database\Seeder;

class TenantTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Criando tenants de teste...');

        // Tenant 1000 - Empresa ABC
        $tenant1000 = new Tenant([
            'id' => '1000',
            'schema_name' => 'tenant_1000',
            'status' => 'active',
            'data' => [
                'name' => 'Empresa ABC',
                'description' => 'Empresa de transporte e logística',
                'cnpj' => '12.345.678/0001-90',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@empresaabc.com',
            ],
            'settings' => [
                'timezone' => 'America/Sao_Paulo',
                'currency' => 'BRL',
                'language' => 'pt_BR',
                'created_by' => 'seeder',
                'created_at' => now()->toISOString(),
            ]
        ]);
        $tenant1000->save();

        // Criar schema se não existir
        if (!$tenant1000->schemaExists()) {
            $tenant1000->createSchema();
            $tenant1000->runMigrations();
        }

        // Tenant 1001 - Empresa XYZ
        $tenant1001 = new Tenant([
            'id' => '1001',
            'schema_name' => 'tenant_1001',
            'status' => 'active',
            'data' => [
                'name' => 'Empresa XYZ',
                'description' => 'Empresa de construção civil',
                'cnpj' => '98.765.432/0001-10',
                'phone' => '(11) 88888-8888',
                'email' => 'contato@empresaxyz.com',
            ],
            'settings' => [
                'timezone' => 'America/Sao_Paulo',
                'currency' => 'BRL',
                'language' => 'pt_BR',
                'created_by' => 'seeder',
                'created_at' => now()->toISOString(),
            ]
        ]);
        $tenant1001->save();

        // Criar schema se não existir
        if (!$tenant1001->schemaExists()) {
            $tenant1001->createSchema();
            $tenant1001->runMigrations();
        }

        $this->command->info('✅ Tenants de teste criados:');
        $this->command->info('  - Tenant 1000: Empresa ABC');
        $this->command->info('  - Tenant 1001: Empresa XYZ');
    }
}
