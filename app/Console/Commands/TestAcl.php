<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use Illuminate\Console\Command;

class TestAcl extends Command
{
    protected $signature = 'acl:test';
    protected $description = 'Testa o sistema de ACL';

    public function handle()
    {
        $this->info('🧪 Testando Sistema de ACL...');
        
        // Verificar se as tabelas existem e têm dados
        $this->info('📊 Verificando dados do sistema...');
        
        $permissionsCount = Permission::count();
        $rolesCount = Role::count();
        $menusCount = Menu::count();
        $usersCount = User::count();
        
        $this->info("✅ Permissões: {$permissionsCount}");
        $this->info("✅ Roles: {$rolesCount}");
        $this->info("✅ Menus: {$menusCount}");
        $this->info("✅ Usuários: {$usersCount}");
        
        // Verificar roles específicos
        $this->info('🔍 Verificando roles...');
        $adminRole = Role::where('name', 'admin')->first();
        $operatorRole = Role::where('name', 'operator')->first();
        
        if ($adminRole) {
            $this->info("✅ Role 'admin' encontrado com {$adminRole->permissions->count()} permissões");
        } else {
            $this->error("❌ Role 'admin' não encontrado");
        }
        
        if ($operatorRole) {
            $this->info("✅ Role 'operator' encontrado com {$operatorRole->permissions->count()} permissões");
        } else {
            $this->error("❌ Role 'operator' não encontrado");
        }
        
        // Verificar usuários e seus roles
        $this->info('👥 Verificando usuários e roles...');
        $users = User::with('roles')->get();
        
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $this->info("👤 {$user->name} ({$user->email}) - Roles: {$roles}");
        }
        
        // Testar método getAccessibleMenus
        $this->info('🍽️ Testando menus acessíveis...');
        $firstUser = $users->first();
        
        if ($firstUser) {
            try {
                $menus = $firstUser->getAccessibleMenus();
                $this->info("✅ Usuário '{$firstUser->name}' tem acesso a {$menus->count()} menus");
                
                foreach ($menus as $menu) {
                    $this->info("  - {$menu->label} ({$menu->secao})");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro ao obter menus: " . $e->getMessage());
            }
        }
        
        // Testar permissões
        $this->info('🔐 Testando permissões...');
        if ($firstUser) {
            $hasAdminUsers = $firstUser->hasPermission('admin.users');
            $hasServicesView = $firstUser->hasPermission('services.view');
            
            $this->info("✅ Usuário '{$firstUser->name}' tem permissão 'admin.users': " . ($hasAdminUsers ? 'SIM' : 'NÃO'));
            $this->info("✅ Usuário '{$firstUser->name}' tem permissão 'services.view': " . ($hasServicesView ? 'SIM' : 'NÃO'));
        }
        
        $this->info('🎉 Teste do sistema de ACL concluído!');
    }
}
