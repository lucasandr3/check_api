<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {tenant? : ID do tenant específico} 
                            {--all : Executar para todos os tenants}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Executar migrations para tenant(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $all = $this->option('all');

        if (!$tenantId && !$all) {
            $this->error('Especifique um tenant ID ou use --all para todos os tenants');
            return 1;
        }

        if ($all) {
            return $this->migrateAllTenants();
        } else {
            return $this->migrateTenant($tenantId);
        }
    }

    /**
     * Executar migrations para um tenant específico
     */
    private function migrateTenant(string $tenantId): int
    {
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' não encontrado");
            return 1;
        }

        $this->info("🔄 Executando migrations para tenant: {$tenant->name} ({$tenant->id})");

        if (!$tenant->schemaExists()) {
            $this->warn("Schema não existe. Criando...");
            if (!$tenant->createSchema()) {
                $this->error("Falha ao criar schema");
                return 1;
            }
        }

        try {
            if ($tenant->runMigrations()) {
                $this->info("✅ Migrations executadas com sucesso para tenant {$tenant->id}");
                return 0;
            } else {
                $this->error("❌ Erro ao executar migrations para tenant {$tenant->id}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Executar migrations para todos os tenants
     */
    private function migrateAllTenants(): int
    {
        $tenants = Tenant::active()->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant ativo encontrado.');
            return 0;
        }

        $this->info("🔄 Executando migrations para {$tenants->count()} tenants...");
        $this->newLine();

        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $this->line("Processando tenant: {$tenant->name} ({$tenant->id})");

            try {
                if (!$tenant->schemaExists()) {
                    $this->warn("  Schema não existe. Criando...");
                    if (!$tenant->createSchema()) {
                        $this->error("  ❌ Falha ao criar schema");
                        $failed++;
                        continue;
                    }
                }

                if ($tenant->runMigrations()) {
                    $this->info("  ✅ Sucesso");
                    $success++;
                } else {
                    $this->error("  ❌ Falha nas migrations");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erro: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("📊 Resultado:");
        $this->info("  ✅ Sucessos: {$success}");
        if ($failed > 0) {
            $this->error("  ❌ Falhas: {$failed}");
        }

        return $failed > 0 ? 1 : 0;
    }
}
