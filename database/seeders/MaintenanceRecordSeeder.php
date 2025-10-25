<?php

namespace Database\Seeders;

use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use App\Models\Equipment;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaintenanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createMaintenanceRecordsForCompany($company);
        }
    }
    
    private function createMaintenanceRecordsForCompany(Company $company): void
    {
        // Buscar um usuário da empresa para associar aos registros
        $user = User::where('company_id', $company->id)->first();
        if (!$user) {
            if ($this->command) {
                $this->command->warn("    ⚠️ {$company->name}: Nenhum usuário encontrado, pulando criação de registros de manutenção");
            }
            return;
        }
        
        // Registros para veículos
        $vehicles = Vehicle::where('company_id', $company->id)->get();
        foreach ($vehicles as $vehicle) {
            $this->createVehicleMaintenanceRecords($vehicle, $user);
        }
        
        // Registros para equipamentos
        $equipment = Equipment::where('company_id', $company->id)->get();
        foreach ($equipment as $equip) {
            $this->createEquipmentMaintenanceRecords($equip, $user);
        }
        
        if ($this->command) {
            $this->command->info("    🔧 {$company->name}: Registros de manutenção criados para {$vehicles->count()} veículos e {$equipment->count()} equipamentos");
        }
    }
    
    private function createVehicleMaintenanceRecords(Vehicle $vehicle, User $user): void
    {
        $records = [
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'preventive',
                'description' => 'Troca de óleo, filtros e verificação geral',
                'parts_used' => [
                    ['name' => 'Óleo Motor', 'quantity' => 1, 'cost' => 120.00],
                    ['name' => 'Filtro de Óleo', 'quantity' => 1, 'cost' => 45.00],
                    ['name' => 'Filtro de Ar', 'quantity' => 1, 'cost' => 35.00],
                ],
                'labor_hours' => 3.5,
                'total_cost' => 200.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(30),
                'status' => 'completed',
                'observations' => 'Veículo em perfeito estado. Próxima revisão em 10.000 km.',
            ],
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'corrective',
                'description' => 'Substituição de pastilhas e discos de freio dianteiros',
                'parts_used' => [
                    ['name' => 'Pastilhas de Freio', 'quantity' => 4, 'cost' => 180.00],
                    ['name' => 'Discos de Freio', 'quantity' => 2, 'cost' => 200.00],
                ],
                'labor_hours' => 2.0,
                'total_cost' => 120.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(15),
                'status' => 'completed',
                'observations' => 'Pastilhas desgastadas. Sistema funcionando normalmente após reparo.',
            ],
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'preventive',
                'description' => 'Substituição de todos os pneus por novos',
                'parts_used' => [
                    ['name' => 'Pneus 225/70R19.5', 'quantity' => 4, 'cost' => 1200.00],
                    ['name' => 'Válvulas', 'quantity' => 4, 'cost' => 20.00],
                ],
                'labor_hours' => 1.5,
                'total_cost' => 80.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(60),
                'status' => 'completed',
                'observations' => 'Pneus com desgaste irregular. Alinhamento realizado.',
            ]
        ];
        
        foreach ($records as $recordData) {
            MaintenanceRecord::create($recordData);
        }
    }
    
    private function createEquipmentMaintenanceRecords(Equipment $equipment, User $user): void
    {
        $records = [
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'preventive',
                'description' => 'Troca de óleo hidráulico e filtros',
                'parts_used' => [
                    ['name' => 'Óleo Hidráulico', 'quantity' => 20, 'cost' => 150.00],
                    ['name' => 'Filtro Hidráulico', 'quantity' => 1, 'cost' => 45.00],
                ],
                'labor_hours' => 2.0,
                'total_cost' => 100.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(20),
                'status' => 'completed',
                'observations' => 'Equipamento funcionando perfeitamente. Sistema hidráulico limpo.',
            ],
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'corrective',
                'description' => 'Correção de problema no sistema de combustão',
                'parts_used' => [
                    ['name' => 'Bomba de Combustível', 'quantity' => 1, 'cost' => 300.00],
                    ['name' => 'Filtro de Combustível', 'quantity' => 1, 'cost' => 25.00],
                ],
                'labor_hours' => 4.0,
                'total_cost' => 200.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(10),
                'status' => 'completed',
                'observations' => 'Problema resolvido. Motor funcionando normalmente.',
            ],
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'preventive',
                'description' => 'Calibração de sensores e testes de funcionamento',
                'parts_used' => [],
                'labor_hours' => 1.5,
                'total_cost' => 150.00,
                'performed_by' => $user->id,
                'performed_at' => $this->getPastDate(45),
                'status' => 'completed',
                'observations' => 'Todos os sensores calibrados. Equipamento aprovado nos testes.',
            ]
        ];
        
        foreach ($records as $recordData) {
            MaintenanceRecord::create($recordData);
        }
    }
    
    private function getPastDate(int $days): string
    {
        return now()->subDays($days)->format('Y-m-d H:i:s');
    }
}