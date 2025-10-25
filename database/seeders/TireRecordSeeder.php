<?php

namespace Database\Seeders;

use App\Models\TireRecord;
use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Database\Seeder;

class TireRecordSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createTireRecordsForCompany($company);
        }
    }
    
    private function createTireRecordsForCompany(Company $company): void
    {
        $vehicles = Vehicle::where('company_id', $company->id)->get();
        
        foreach ($vehicles as $vehicle) {
            $this->createTiresForVehicle($vehicle);
        }
        
        if ($this->command) {
            $this->command->info("    🛞 {$company->name}: Pneus criados para {$vehicles->count()} veículos");
        }
    }
    
    private function createTiresForVehicle(Vehicle $vehicle): void
    {
        $tirePositions = [
            ['position' => 'front_left', 'name' => 'Dianteiro Esquerdo'],
            ['position' => 'front_right', 'name' => 'Dianteiro Direito'],
            ['position' => 'rear_left', 'name' => 'Traseiro Esquerdo'],
            ['position' => 'rear_right', 'name' => 'Traseiro Direito'],
            ['position' => 'spare', 'name' => 'Socorro'],
        ];
        
        foreach ($tirePositions as $position) {
            TireRecord::create([
                'tenant_id' => $vehicle->tenant_id,
                'company_id' => $vehicle->company_id,
                'vehicle_id' => $vehicle->id,
                'tire_position' => $position['position'],
                'tire_brand' => $this->getRandomBrand(),
                'tire_model' => $this->getRandomModel(),
                'tire_size' => $this->getRandomSize(),
                'installation_date' => $this->getRandomInstallationDate(),
                'installation_km' => rand(10000, 50000),
                'tread_depth_new' => rand(8, 12),
                'cost' => rand(300, 800),
                'warranty_km' => rand(60000, 80000),
                'status' => $this->getRandomStatus(),
                'observations' => 'Pneu instalado conforme especificação do veículo',
            ]);
        }
    }
    
    private function getRandomBrand(): string
    {
        $brands = ['Michelin', 'Bridgestone', 'Pirelli', 'Continental', 'Goodyear', 'Dunlop', 'Firestone'];
        return $brands[array_rand($brands)];
    }
    
    private function getRandomModel(): string
    {
        $models = ['XZE', 'XDE', 'XZE2', 'XDE2', 'XZE3', 'XDE3', 'XZE4', 'XDE4'];
        return $models[array_rand($models)];
    }
    
    private function getRandomSize(): string
    {
        $sizes = ['225/70R19.5', '245/70R19.5', '265/70R19.5', '285/70R19.5', '315/70R22.5'];
        return $sizes[array_rand($sizes)];
    }
    
    private function getRandomInstallationDate(): string
    {
        $year = rand(2022, 2024);
        $month = rand(1, 12);
        $day = rand(1, 28);
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    
    private function getRandomStatus(): string
    {
        $statuses = ['active', 'removed', 'rotated'];
        return $statuses[array_rand($statuses)];
    }
}