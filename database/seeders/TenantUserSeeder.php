<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar todos os tenants ativos
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->command->info("👤 Criando usuários para tenant: {$tenant->data['name']} ({$tenant->id})");
            
            // Ativar o tenant
            $tenant->makeCurrent();
            
            // Criar usuários de teste
            $users = [
                [
                    'name' => 'Admin ' . $tenant->data['name'],
                    'email' => 'admin@tenant' . $tenant->id . '.com',
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'role' => 'admin'
                ],
                [
                    'name' => 'Operador ' . $tenant->data['name'],
                    'email' => 'operador@tenant' . $tenant->id . '.com',
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'role' => 'operator'
                ]
            ];
            
            foreach ($users as $userData) {
                $roleName = $userData['role'];
                unset($userData['role']);
                
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );
                
                // Associar role ao usuário
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->sync([$role->id]);
                }
                
                $this->command->info("  ✅ Usuário criado: {$user->email} ({$roleName})");
            }
            
            // Resetar tenant
            Tenant::forgetCurrent();
            
            $this->command->info("✅ Usuários criados para tenant {$tenant->id}");
        }
        
        $this->command->info("🎉 Usuários criados para todos os tenants!");
    }
}