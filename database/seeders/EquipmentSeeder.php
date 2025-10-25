<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Company;
use App\Models\Client;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createEquipmentForCompany($company);
        }
    }
    
    private function createEquipmentForCompany(Company $company): void
    {
        // Buscar um cliente da empresa para associar aos equipamentos
        $client = Client::where('company_id', $company->id)->first();
        if (!$client) {
            if ($this->command) {
                $this->command->warn("    ⚠️ {$company->name}: Nenhum cliente encontrado, pulando criação de equipamentos");
            }
            return;
        }
        
        $equipment = [
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Empilhadeira Toyota',
                'type' => 'Empilhadeira',
                'brand' => 'Toyota',
                'model' => 'FDZ50',
                'serial_number' => 'TOY-FDZ50-001',
                'acquisition_date' => '2021-04-15',
                'warranty_expiration' => '2024-04-15',
                'status' => 'active',
                'location' => 'Galpão Principal',
                'observations' => 'Equipamento em perfeito estado',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Guincho Hidráulico',
                'type' => 'Guincho',
                'brand' => 'Indústria Brasileira',
                'model' => 'GH-5000',
                'serial_number' => 'GH-5000-002',
                'acquisition_date' => '2020-08-10',
                'warranty_expiration' => '2023-08-10',
                'status' => 'active',
                'location' => 'Área de Manutenção',
                'observations' => 'Revisão realizada em julho/2024',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Retroescavadeira',
                'type' => 'Retroescavadeira',
                'brand' => 'Case',
                'model' => '580',
                'serial_number' => 'CASE-580-003',
                'acquisition_date' => '2019-12-05',
                'warranty_expiration' => '2022-12-05',
                'status' => 'maintenance',
                'location' => 'Oficina',
                'observations' => 'Em manutenção - problema no motor',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Compressor de Ar',
                'type' => 'Compressor',
                'brand' => 'Atlas Copco',
                'model' => 'CA-100',
                'serial_number' => 'CA-100-004',
                'acquisition_date' => '2022-01-20',
                'warranty_expiration' => '2025-01-20',
                'status' => 'active',
                'location' => 'Oficina',
                'observations' => 'Funcionando perfeitamente',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Gerador Diesel',
                'type' => 'Gerador',
                'brand' => 'Perkins',
                'model' => 'GD-30KVA',
                'serial_number' => 'GD-30KVA-005',
                'acquisition_date' => '2021-06-30',
                'warranty_expiration' => '2024-06-30',
                'status' => 'active',
                'location' => 'Casa de Máquinas',
                'observations' => 'Teste mensal realizado',
            ],
            [
                'company_id' => $company->id,
                'client_id' => $client->id,
                'name' => 'Betoneira',
                'type' => 'Betoneira',
                'brand' => 'Tramontina',
                'model' => 'BT-400L',
                'serial_number' => 'BT-400L-006',
                'acquisition_date' => '2020-03-15',
                'warranty_expiration' => '2023-03-15',
                'status' => 'active',
                'location' => 'Canteiro de Obras',
                'observations' => 'Limpeza diária realizada',
            ]
        ];
        
        foreach ($equipment as $equipmentData) {
            $equipmentData['tenant_id'] = $company->tenant_id;
            Equipment::create($equipmentData);
        }
        
        if ($this->command) {
            $this->command->info("    🔧 {$company->name}: 6 equipamentos criados");
        }
    }
}