<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Criando usuários de teste...');

        // Usuários para Tenant 1000 (Empresa ABC)
        $this->createUsersForTenant('1000', [
            [
                'name' => 'Admin ABC',
                'email' => 'admin@empresaabc.com',
                'password' => '123456',
                'role' => 'super_admin',
                'phone' => '(11) 99999-9999',
            ],
            [
                'name' => 'Gerente ABC',
                'email' => 'gerente@empresaabc.com',
                'password' => '123456',
                'role' => 'admin',
                'phone' => '(11) 99999-9998',
            ],
            [
                'name' => 'Operador ABC',
                'email' => 'operador@empresaabc.com',
                'password' => '123456',
                'role' => 'operator',
                'phone' => '(11) 99999-9997',
            ],
        ]);

        // Usuários para Tenant 1001 (Empresa XYZ)
        $this->createUsersForTenant('1001', [
            [
                'name' => 'Admin XYZ',
                'email' => 'admin@empresaxyz.com',
                'password' => '123456',
                'role' => 'super_admin',
                'phone' => '(11) 88888-8888',
            ],
            [
                'name' => 'Gerente XYZ',
                'email' => 'gerente@empresaxyz.com',
                'password' => '123456',
                'role' => 'admin',
                'phone' => '(11) 88888-8887',
            ],
            [
                'name' => 'Operador XYZ',
                'email' => 'operador@empresaxyz.com',
                'password' => '123456',
                'role' => 'operator',
                'phone' => '(11) 88888-8886',
            ],
        ]);

        $this->command->info('✅ Usuários de teste criados!');
        $this->command->info('');
        $this->command->info('🔑 Credenciais de Login:');
        $this->command->info('');
        $this->command->info('📋 TENANT 1000 (Empresa ABC):');
        $this->command->info('  👤 admin@empresaabc.com / 123456 (Super Admin)');
        $this->command->info('  👤 gerente@empresaabc.com / 123456 (Admin)');
        $this->command->info('  👤 operador@empresaabc.com / 123456 (Operador)');
        $this->command->info('');
        $this->command->info('📋 TENANT 1001 (Empresa XYZ):');
        $this->command->info('  👤 admin@empresaxyz.com / 123456 (Super Admin)');
        $this->command->info('  👤 gerente@empresaxyz.com / 123456 (Admin)');
        $this->command->info('  👤 operador@empresaxyz.com / 123456 (Operador)');
    }

    /**
     * Criar usuários para um tenant específico
     */
    private function createUsersForTenant(string $tenantId, array $users): void
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->command->error("Tenant {$tenantId} não encontrado!");
            return;
        }

        // Ativar tenant para criar usuários no schema correto
        $tenant->makeCurrent();

        foreach ($users as $userData) {
            // Verificar se usuário já existe
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                $this->command->warn("Usuário {$userData['email']} já existe, pulando...");
                continue;
            }

            // Criar usuário
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'tenant_id' => $tenantId,
                'email_verified_at' => now(),
            ]);

            // Atribuir role (se sistema de roles estiver configurado)
            $this->assignRoleToUser($user, $userData['role']);

            $this->command->info("✅ Usuário criado: {$user->email} (Tenant: {$tenantId})");
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
            if (!DB::getSchemaBuilder()->hasTable('roles')) {
                return;
            }

            // Buscar role
            $role = DB::table('roles')->where('name', $roleName)->first();
            if (!$role) {
                $this->command->warn("Role '{$roleName}' não encontrada para usuário {$user->email}");
                return;
            }

            // Verificar se tabela role_user existe
            if (!DB::getSchemaBuilder()->hasTable('role_user')) {
                return;
            }

            // Atribuir role
            DB::table('role_user')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        } catch (\Exception $e) {
            $this->command->warn("Erro ao atribuir role '{$roleName}' para {$user->email}: " . $e->getMessage());
        }
    }
}
