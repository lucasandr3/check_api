<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class TenantAclSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar todos os tenants ativos
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->command->info("📋 Executando ACL para tenant: {$tenant->data['name']} ({$tenant->id})");
            
            // Ativar o tenant
            $tenant->makeCurrent();
            
            // Executar o ACLSeeder para este tenant
            $this->call(AclSeeder::class);
            
            // Resetar tenant
            Tenant::forgetCurrent();
            
            $this->command->info("✅ ACL executado para tenant {$tenant->id}");
        }
        
        $this->command->info("🎉 ACL executado para todos os tenants!");
    }
}