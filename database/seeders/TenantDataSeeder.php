<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📊 Criando dados de teste para tenants...');

        // Executar seeders para cada tenant
        $this->seedTenantData('1000', 'Empresa ABC');
        $this->seedTenantData('1001', 'Empresa XYZ');

        $this->command->info('✅ Dados de teste criados para todos os tenants!');
    }

    /**
     * Criar dados para um tenant específico
     */
    private function seedTenantData(string $tenantId, string $tenantName): void
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->command->error("Tenant {$tenantId} não encontrado!");
            return;
        }

        $this->command->info("📋 Criando dados para {$tenantName} (Tenant {$tenantId})...");

        // Ativar tenant
        $tenant->makeCurrent();

        try {
            // 1. Criar menus
            $this->createMenus($tenantName);
            
            // 2. Criar escritórios
            $this->createOffices($tenantName);

        } catch (\Exception $e) {
            $this->command->error("Erro ao criar dados para tenant {$tenantId}: " . $e->getMessage());
        } finally {
            // Resetar tenant
            Tenant::forgetCurrent();
        }
    }

    /**
     * Criar menus do sistema
     */
    private function createMenus(string $tenantName): void
    {
        if (!DB::getSchemaBuilder()->hasTable('menus')) {
            return;
        }

        $menus = [
            // CHECKLIST & INSPEÇÃO
            ['label' => 'Dashboard', 'url' => '/dashboard', 'icone' => 'dashboard', 'order' => 1, 'secao' => 'CHECKLIST & INSPEÇÃO'],
            ['label' => 'Checklists', 'url' => '/checklists', 'icone' => 'checklist', 'order' => 2, 'secao' => 'CHECKLIST & INSPEÇÃO'],
            ['label' => 'Templates', 'url' => '/checklist-templates', 'icone' => 'template', 'order' => 3, 'secao' => 'CHECKLIST & INSPEÇÃO'],
            
            // GESTÃO DE FROTA
            ['label' => 'Veículos', 'url' => '/vehicles', 'icone' => 'vehicle', 'order' => 4, 'secao' => 'GESTÃO DE FROTA'],
            ['label' => 'Equipamentos', 'url' => '/equipment', 'icone' => 'equipment', 'order' => 5, 'secao' => 'GESTÃO DE FROTA'],
            ['label' => 'Pneus', 'url' => '/tires', 'icone' => 'tire', 'order' => 6, 'secao' => 'GESTÃO DE FROTA'],
            
            // MANUTENÇÃO
            ['label' => 'Manutenção', 'url' => '/maintenance', 'icone' => 'maintenance', 'order' => 7, 'secao' => 'MANUTENÇÃO'],
            ['label' => 'Agendamentos', 'url' => '/maintenance-schedules', 'icone' => 'schedule', 'order' => 8, 'secao' => 'MANUTENÇÃO'],
            
            // RELATÓRIOS
            ['label' => 'Relatórios', 'url' => '/reports', 'icone' => 'report', 'order' => 9, 'secao' => 'RELATÓRIOS'],
            
            // ADMINISTRAÇÃO
            ['label' => 'Usuários', 'url' => '/users', 'icone' => 'users', 'order' => 10, 'secao' => 'ADMINISTRAÇÃO'],
            ['label' => 'Permissões', 'url' => '/permissions', 'icone' => 'permissions', 'order' => 11, 'secao' => 'ADMINISTRAÇÃO'],
        ];

        foreach ($menus as $menuData) {
            DB::table('menus')->insertOrIgnore([
                'label' => $menuData['label'],
                'url' => $menuData['url'],
                'icone' => $menuData['icone'],
                'order' => $menuData['order'],
                'secao' => $menuData['secao'],
                'identificador' => strtolower(str_replace(' ', '-', $menuData['label'])),
                'rotas_ativas' => json_encode([$menuData['url']]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ Menus criados");
    }

    /**
     * Criar escritórios
     */
    private function createOffices(string $tenantName): void
    {
        if (!DB::getSchemaBuilder()->hasTable('offices')) {
            return;
        }

        $offices = [
            [
                'name' => 'Matriz São Paulo',
                'address' => 'Rua das Flores, 123 - São Paulo/SP',
                'phone' => '(11) 99999-9999',
                'email' => 'matriz@' . strtolower(str_replace(' ', '', $tenantName)) . '.com',
            ],
            [
                'name' => 'Filial Rio de Janeiro',
                'address' => 'Av. Copacabana, 456 - Rio de Janeiro/RJ',
                'phone' => '(21) 88888-8888',
                'email' => 'rj@' . strtolower(str_replace(' ', '', $tenantName)) . '.com',
            ],
        ];

        foreach ($offices as $officeData) {
            DB::table('offices')->insertOrIgnore([
                'name' => $officeData['name'],
                'address' => $officeData['address'],
                'phone' => $officeData['phone'],
                'email' => $officeData['email'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ Escritórios criados");
    }
}