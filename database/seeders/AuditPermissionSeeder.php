<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AuditPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissão de auditoria
        $auditPermission = Permission::firstOrCreate([
            'name' => 'admin.audit'
        ], [
            'display_name' => 'Gerenciar Auditoria',
            'description' => 'Permite visualizar e exportar logs de auditoria do sistema'
        ]);

        // Atribuir permissão ao role admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->attach($auditPermission->id);
        }

        // Atribuir permissão ao role manager se existir
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->permissions()->attach($auditPermission->id);
        }

        // Atribuir permissão ao role super_admin se existir
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->permissions()->attach($auditPermission->id);
        }

        $this->command->info('Permissões de auditoria criadas com sucesso!');
    }
}
