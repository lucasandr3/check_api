<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            if ($this->command) {
                $this->command->info("📊 Criando dados de teste para tenant: {$tenant->data['name']} ({$tenant->id})");
            }
            
            // Ativar o tenant
            $tenant->makeCurrent();
            
            // Executar todos os seeders de dados de teste
            $this->call(ClientSeeder::class);
            $this->call(VehicleSeeder::class);
            $this->call(EquipmentSeeder::class);
            $this->call(TireRecordSeeder::class);
            $this->call(MaintenanceScheduleSeeder::class);
            $this->call(MaintenanceRecordSeeder::class);
            
            // Resetar tenant
            Tenant::forgetCurrent();
            
            if ($this->command) {
                $this->command->info("✅ Dados de teste criados para tenant {$tenant->id}");
            }
        }
        
        if ($this->command) {
            $this->command->info("🎉 Todos os dados de teste foram criados!");
        }
    }
}
