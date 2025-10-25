<?php

namespace Database\Seeders;

use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use App\Models\Equipment;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createMaintenanceSchedulesForCompany($company);
        }
    }
    
    private function createMaintenanceSchedulesForCompany(Company $company): void
    {
        // Agendamentos para veículos
        $vehicles = Vehicle::where('company_id', $company->id)->get();
        foreach ($vehicles as $vehicle) {
            $this->createVehicleMaintenanceSchedules($vehicle);
        }
        
        // Agendamentos para equipamentos
        $equipment = Equipment::where('company_id', $company->id)->get();
        foreach ($equipment as $equip) {
            $this->createEquipmentMaintenanceSchedules($equip);
        }
        
        if ($this->command) {
            $this->command->info("    📅 {$company->name}: Agendamentos criados para {$vehicles->count()} veículos e {$equipment->count()} equipamentos");
        }
    }
    
    private function createVehicleMaintenanceSchedules(Vehicle $vehicle): void
    {
        $schedules = [
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'preventive',
                'name' => 'Revisão Preventiva - 10.000 km',
                'description' => 'Troca de óleo, filtros e verificação geral do veículo',
                'frequency_type' => 'km',
                'frequency_value' => 10000,
                'next_due_date' => $this->getFutureDate(30),
                'next_due_km' => $vehicle->current_km + 10000,
                'is_active' => true,
                'priority' => 'medium',
                'estimated_cost' => rand(500, 1200),
                'estimated_hours' => 4,
            ],
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'preventive',
                'name' => 'Revisão Preventiva - 20.000 km',
                'description' => 'Revisão completa com troca de filtros e óleo',
                'frequency_type' => 'km',
                'frequency_value' => 20000,
                'next_due_date' => $this->getFutureDate(60),
                'next_due_km' => $vehicle->current_km + 20000,
                'is_active' => true,
                'priority' => 'high',
                'estimated_cost' => rand(800, 1500),
                'estimated_hours' => 8,
            ],
            [
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'maintainable_type' => Vehicle::class,
                'maintainable_id' => $vehicle->id,
                'type' => 'corrective',
                'name' => 'Reparo no Sistema de Freios',
                'description' => 'Substituição de pastilhas e discos de freio',
                'frequency_type' => 'days',
                'frequency_value' => 7,
                'next_due_date' => $this->getFutureDate(7),
                'is_active' => true,
                'priority' => 'high',
                'estimated_cost' => rand(300, 600),
                'estimated_hours' => 3,
            ]
        ];
        
        foreach ($schedules as $scheduleData) {
            MaintenanceSchedule::create($scheduleData);
        }
    }
    
    private function createEquipmentMaintenanceSchedules(Equipment $equipment): void
    {
        $schedules = [
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'preventive',
                'name' => 'Manutenção Preventiva - 500h',
                'description' => 'Troca de óleo hidráulico e filtros',
                'frequency_type' => 'hours',
                'frequency_value' => 500,
                'next_due_date' => $this->getFutureDate(45),
                'is_active' => true,
                'priority' => 'medium',
                'estimated_cost' => rand(200, 500),
                'estimated_hours' => 2,
            ],
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'preventive',
                'name' => 'Manutenção Preventiva - 1000h',
                'description' => 'Revisão completa do equipamento',
                'frequency_type' => 'hours',
                'frequency_value' => 1000,
                'next_due_date' => $this->getFutureDate(90),
                'is_active' => true,
                'priority' => 'high',
                'estimated_cost' => rand(600, 1200),
                'estimated_hours' => 6,
            ],
            [
                'tenant_id' => $equipment->tenant_id,
                'company_id' => $equipment->company_id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'type' => 'corrective',
                'name' => 'Reparo no Motor',
                'description' => 'Correção de problema no sistema de combustão',
                'frequency_type' => 'days',
                'frequency_value' => 14,
                'next_due_date' => $this->getFutureDate(14),
                'is_active' => true,
                'priority' => 'high',
                'estimated_cost' => rand(800, 2000),
                'estimated_hours' => 8,
            ]
        ];
        
        foreach ($schedules as $scheduleData) {
            MaintenanceSchedule::create($scheduleData);
        }
    }
    
    private function getFutureDate(int $days): string
    {
        return now()->addDays($days)->format('Y-m-d');
    }
}