<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Company;
use Illuminate\Console\Command;

class TestCompanySystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:company-system {tenant : ID do tenant}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Testar o sistema de empresas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' não encontrado");
            return 1;
        }

        $this->info("🧪 Testando sistema de empresas para tenant: {$tenant->name} ({$tenant->id})");

        if (!$tenant->schemaExists()) {
            $this->error("Schema não existe. Execute 'php artisan tenant:migrate {$tenantId}' primeiro");
            return 1;
        }

        try {
            $tenant->makeCurrent();

            // Verificar se a tabela companies existe
            if (!\Schema::hasTable('companies')) {
                $this->error("❌ Tabela 'companies' não existe. Execute a migration primeiro.");
                return 1;
            }

            $this->info("✅ Tabela 'companies' existe");

            // Contar empresas existentes
            $companyCount = Company::count();
            $this->info("📊 Empresas existentes: {$companyCount}");

            if ($companyCount == 0) {
                $this->warn("⚠️  Nenhuma empresa encontrada. Criando empresas de exemplo...");
                
                // Criar empresas de exemplo
                Company::create([
                    'tenant_id' => $tenantId,
                    'name' => 'Empresa Principal',
                    'address' => 'Rua Principal, 123 - Centro',
                    'phone' => '(11) 99999-9999',
                    'email' => 'contato@empresaprincipal.com',
                    'cnpj' => '12.345.678/0001-90',
                ]);

                Company::create([
                    'tenant_id' => $tenantId,
                    'name' => 'Filial São Paulo',
                    'address' => 'Av. Paulista, 1000 - Bela Vista',
                    'phone' => '(11) 88888-8888',
                    'email' => 'sp@empresaprincipal.com',
                    'cnpj' => '12.345.678/0002-71',
                ]);

                $this->info("✅ Empresas de exemplo criadas");
            }

            // Listar empresas
            $companies = Company::all();
            $this->info("📋 Empresas disponíveis:");
            foreach ($companies as $company) {
                $this->line("  - ID: {$company->id} | Nome: {$company->name} | CNPJ: {$company->cnpj}");
            }

            $this->info("✅ Sistema de empresas funcionando corretamente!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        } finally {
            Tenant::forgetCurrent();
        }
    }
}
