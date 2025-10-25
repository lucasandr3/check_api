<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Client;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createVehiclesForCompany($company);
        }
    }
    
    private function createVehiclesForCompany(Company $company): void
    {
        // Buscar um cliente da empresa para associar aos veículos
        $client = Client::where('company_id', $company->id)->first();
        if (!$client) {
            if ($this->command) {
                $this->command->warn("    ⚠️ {$company->name}: Nenhum cliente encontrado, pulando criação de veículos");
            }
            return;
        }
        
        $vehicles = [
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'brand' => 'Volkswagen',
                'model' => 'Delivery',
                'year' => 2020,
                'color' => 'Branco',
                'plate' => 'ABC-1234',
                'chassis' => '9BWZZZZZZZZZZZZZZ',
                'fuel_type' => 'diesel',
                'engine' => '2.8L',
                'transmission' => 'manual',
                'category' => 'truck',
                'current_km' => 45000,
                'acquisition_date' => '2020-01-15',
                'license_expiration' => '2025-01-15',
                'insurance_expiration' => '2025-01-15',
                'status' => 'active',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'brand' => 'Mercedes',
                'model' => 'Sprinter',
                'year' => 2021,
                'color' => 'Prata',
                'plate' => 'DEF-5678',
                'chassis' => 'WDB9066321L123456',
                'fuel_type' => 'diesel',
                'engine' => '2.2L',
                'transmission' => 'manual',
                'category' => 'van',
                'current_km' => 32000,
                'acquisition_date' => '2021-03-20',
                'license_expiration' => '2025-03-20',
                'insurance_expiration' => '2025-03-20',
                'status' => 'active',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'brand' => 'Ford',
                'model' => 'Transit',
                'year' => 2019,
                'color' => 'Azul',
                'plate' => 'GHI-9012',
                'chassis' => '1FTBW2CM5KKA12345',
                'fuel_type' => 'diesel',
                'engine' => '2.0L',
                'transmission' => 'manual',
                'category' => 'van',
                'current_km' => 68000,
                'acquisition_date' => '2019-06-10',
                'license_expiration' => '2025-06-10',
                'insurance_expiration' => '2025-06-10',
                'status' => 'maintenance',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'brand' => 'Iveco',
                'model' => 'Daily',
                'year' => 2022,
                'color' => 'Vermelho',
                'plate' => 'JKL-3456',
                'chassis' => 'ZCFC70A0001234567',
                'fuel_type' => 'diesel',
                'engine' => '3.0L',
                'transmission' => 'manual',
                'category' => 'truck',
                'current_km' => 18000,
                'acquisition_date' => '2022-02-28',
                'license_expiration' => '2025-02-28',
                'insurance_expiration' => '2025-02-28',
                'status' => 'active',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'brand' => 'Scania',
                'model' => 'P270',
                'year' => 2020,
                'color' => 'Amarelo',
                'plate' => 'MNO-7890',
                'chassis' => 'YS2K4X20001234567',
                'fuel_type' => 'diesel',
                'engine' => '5.0L',
                'transmission' => 'manual',
                'category' => 'truck',
                'current_km' => 52000,
                'acquisition_date' => '2020-11-12',
                'license_expiration' => '2025-11-12',
                'insurance_expiration' => '2025-11-12',
                'status' => 'active',
            ]
        ];
        
        foreach ($vehicles as $vehicleData) {
            $vehicleData['tenant_id'] = $company->tenant_id;
            Vehicle::create($vehicleData);
        }
        
        if ($this->command) {
            $this->command->info("    🚛 {$company->name}: 5 veículos criados");
        }
    }
}