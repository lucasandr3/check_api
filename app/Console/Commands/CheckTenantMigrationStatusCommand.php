<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckTenantMigrationStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migration-status {tenant : ID do tenant}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Verificar status das migrations do tenant';

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

        $this->info("🔍 Verificando status das migrations para tenant: {$tenant->name} ({$tenant->id})");

        if (!$tenant->schemaExists()) {
            $this->error("❌ Schema não existe. Execute 'php artisan tenant:migrate {$tenantId}' primeiro");
            return 1;
        }

        try {
            $tenant->makeCurrent();

            // Verificar se a tabela offices existe
            if (\Schema::hasTable('offices')) {
                $this->warn("⚠️  Tabela 'offices' ainda existe (não foi renomeada)");
                
                // Verificar se tem coluna cnpj
                if (\Schema::hasColumn('offices', 'cnpj')) {
                    $this->info("✅ Coluna 'cnpj' existe na tabela offices");
                } else {
                    $this->warn("⚠️  Coluna 'cnpj' não existe na tabela offices");
                }
            }

            // Verificar se a tabela companies existe
            if (\Schema::hasTable('companies')) {
                $this->info("✅ Tabela 'companies' existe");
                
                // Verificar se tem coluna cnpj
                if (\Schema::hasColumn('companies', 'cnpj')) {
                    $this->info("✅ Coluna 'cnpj' existe na tabela companies");
                } else {
                    $this->warn("⚠️  Coluna 'cnpj' não existe na tabela companies");
                }
            } else {
                $this->warn("⚠️  Tabela 'companies' não existe");
            }

            // Contar registros
            if (\Schema::hasTable('offices')) {
                $officeCount = \DB::table('offices')->count();
                $this->info("📊 Registros na tabela 'offices': {$officeCount}");
            }

            if (\Schema::hasTable('companies')) {
                $companyCount = \DB::table('companies')->count();
                $this->info("📊 Registros na tabela 'companies': {$companyCount}");
            }

            $this->info("✅ Verificação concluída!");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        } finally {
            Tenant::forgetCurrent();
        }
    }
}
