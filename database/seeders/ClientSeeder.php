<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createClientsForCompany($company);
        }
    }
    
    private function createClientsForCompany(Company $company): void
    {
        $clients = [
            [
                'company_id' => $company->id,
                'name' => 'Cliente Padrão',
                'email' => 'cliente@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                'phone' => '(11) 99999-0001',
                'cpf_cnpj' => '123.456.789-00',
                'address' => 'Rua das Flores, 123 - Centro',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Empresa ABC Ltda',
                'email' => 'contato@empresaabc.com',
                'phone' => '(11) 99999-0002',
                'cpf_cnpj' => '12.345.678/0001-90',
                'address' => 'Av. Paulista, 1000 - Bela Vista',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Transportadora XYZ',
                'email' => 'logistica@transportadoraxyz.com',
                'phone' => '(21) 99999-0003',
                'cpf_cnpj' => '98.765.432/0001-10',
                'address' => 'Rua da Carioca, 456 - Centro',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Distribuidora Nacional',
                'email' => 'vendas@distribuidoranacional.com',
                'phone' => '(31) 99999-0004',
                'cpf_cnpj' => '11.222.333/0001-44',
                'address' => 'Av. Afonso Pena, 2000 - Savassi',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Logística Express',
                'email' => 'admin@logisticaexpress.com',
                'phone' => '(41) 99999-0005',
                'cpf_cnpj' => '55.666.777/0001-88',
                'address' => 'Rua XV de Novembro, 789 - Centro',
            ]
        ];
        
        foreach ($clients as $clientData) {
            $clientData['tenant_id'] = $company->tenant_id;
            Client::create($clientData);
        }
        
        if ($this->command) {
            $this->command->info("    👥 {$company->name}: 5 clientes criados");
        }
    }
}
