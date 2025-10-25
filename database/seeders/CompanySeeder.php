<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter o tenant atual
        $currentTenant = Tenant::current();
        $tenantId = $currentTenant ? $currentTenant->id : '1000';

        $companies = [
            [
                'tenant_id' => $tenantId,
                'name' => 'Empresa Principal',
                'address' => 'Rua Principal, 123 - Centro',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@empresaprincipal.com',
                'cnpj' => '12.345.678/0001-90',
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Filial São Paulo',
                'address' => 'Av. Paulista, 1000 - Bela Vista',
                'phone' => '(11) 88888-8888',
                'email' => 'sp@empresaprincipal.com',
                'cnpj' => '12.345.678/0002-71',
            ],
            [
                'tenant_id' => $tenantId,
                'name' => 'Filial Rio de Janeiro',
                'address' => 'Rua da Carioca, 456 - Centro',
                'phone' => '(21) 77777-7777',
                'email' => 'rj@empresaprincipal.com',
                'cnpj' => '12.345.678/0003-52',
            ],
        ];

        foreach ($companies as $companyData) {
            Company::create($companyData);
        }
    }
}
