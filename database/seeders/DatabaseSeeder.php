<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Office;
use App\Models\User;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Checklist;
use App\Models\ChecklistPhoto;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executar seeders de ACL primeiro
        $this->call(AclSeeder::class);
        $this->call(AuditPermissionSeeder::class);
        
        $this->command->info('✅ Seeders centrais executados!');
        $this->command->info('💡 Para criar dados completos, execute: php artisan setup:project');
    }
}
