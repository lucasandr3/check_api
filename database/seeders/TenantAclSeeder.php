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
        // Executar ACL apenas uma vez no schema público
        // (não precisa ser executado para cada tenant)
        $this->call(AclSeeder::class);
        
        if ($this->command) {
            $this->command->info("🎉 ACL executado no schema público!");
        }
    }
}