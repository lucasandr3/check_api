<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserWithTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Criando usuários com tenant_id...');

        // Buscar todos os tenants ativos
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->command->error('❌ Nenhum tenant ativo encontrado!');
            $this->command->info('💡 Execute primeiro: php artisan tenant:create 1000 "Empresa Teste"');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->createUsersForTenant($tenant);
        }

        $this->command->info('✅ Usuários criados com sucesso!');
        $this->command->info('');
        $this->command->info('🔑 Credenciais de Login:');
        $this->command->info('');

        foreach ($tenants as $tenant) {
            $this->command->info("📋 TENANT {$tenant->id} ({$tenant->data['name']}):");
            $this->command->info("  👤 admin@tenant{$tenant->id}.com / password (Admin)");
            $this->command->info("  👤 operador@tenant{$tenant->id}.com / password (Operador)");
            $this->command->info('');
        }
    }

    /**
     * Criar usuários para um tenant específico
     */
    private function createUsersForTenant(Tenant $tenant): void
    {
        $this->command->info("🔍 Processando tenant: {$tenant->data['name']} ({$tenant->id})");

        // Ativar o tenant
        $tenant->makeCurrent();

        // Buscar primeira empresa do tenant (se existir)
        $company = Company::where('tenant_id', $tenant->id)->first();

        // Usuários padrão para cada tenant
        $users = [
            [
                'name' => 'Admin ' . $tenant->data['name'],
                'email' => 'admin@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'company_id' => $company ? $company->id : null,
                'role' => 'admin'
            ],
            [
                'name' => 'Operador ' . $tenant->data['name'],
                'email' => 'operador@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'company_id' => $company ? $company->id : null,
                'role' => 'operator'
            ],
            [
                'name' => 'Gerente ' . $tenant->data['name'],
                'email' => 'gerente@tenant' . $tenant->id . '.com',
                'password' => Hash::make('password'),
                'tenant_id' => $tenant->id,
                'company_id' => $company ? $company->id : null,
                'role' => 'manager'
            ]
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            // Verificar se usuário já existe
            $existingUser = User::where('email', $userData['email'])->first();
            
            if ($existingUser) {
                // Atualizar tenant_id se não tiver
                if (!$existingUser->tenant_id) {
                    $existingUser->update(['tenant_id' => $tenant->id]);
                    $this->command->info("  🔄 Usuário atualizado: {$existingUser->email}");
                } else {
                    $this->command->warn("  ⚠️ Usuário já existe: {$existingUser->email}");
                }
                continue;
            }

            // Criar novo usuário
            $user = User::create($userData);

            // Associar role ao usuário (se sistema de roles estiver configurado)
            $this->assignRoleToUser($user, $roleName);

            $this->command->info("  ✅ Usuário criado: {$user->email} ({$roleName})");
        }

        // Resetar tenant
        Tenant::forgetCurrent();
    }

    /**
     * Atribuir role ao usuário
     */
    private function assignRoleToUser(User $user, string $roleName): void
    {
        try {
            // Verificar se tabela roles existe
            if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                return;
            }

            // Buscar role
            $role = \App\Models\Role::where('name', $roleName)->first();
            if (!$role) {
                $this->command->warn("  ⚠️ Role '{$roleName}' não encontrada para usuário {$user->email}");
                return;
            }

            // Verificar se tabela role_user existe
            if (!\Illuminate\Support\Facades\Schema::hasTable('role_user')) {
                return;
            }

            // Atribuir role
            $user->roles()->sync([$role->id]);

        } catch (\Exception $e) {
            $this->command->warn("  ⚠️ Erro ao atribuir role '{$roleName}' para {$user->email}: " . $e->getMessage());
        }
    }
}
