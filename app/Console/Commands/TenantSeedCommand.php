<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed 
                            {tenant : ID do tenant específico} 
                            {--class= : Classe do seeder específico}
                            {--all : Executar para todos os tenants}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Executar seeders para tenant(s)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $seederClass = $this->option('class');
        $all = $this->option('all');

        if (!$tenantId && !$all) {
            $this->error('Especifique um tenant ID ou use --all para todos os tenants');
            return 1;
        }

        if ($all) {
            return $this->seedAllTenants($seederClass);
        } else {
            return $this->seedTenant($tenantId, $seederClass);
        }
    }

    /**
     * Executar seeders para um tenant específico
     */
    private function seedTenant(string $tenantId, ?string $seederClass = null): int
    {
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' não encontrado");
            return 1;
        }

        $this->info("🌱 Executando seeders para tenant: {$tenant->name} ({$tenant->id})");

        if (!$tenant->schemaExists()) {
            $this->error("Schema não existe. Execute 'php artisan tenant:migrate {$tenantId}' primeiro");
            return 1;
        }

        try {
            $tenant->makeCurrent();

            if ($seederClass) {
                $this->info("Executando seeder: {$seederClass}");
                Artisan::call('db:seed', ['--class' => $seederClass]);
            } else {
                $this->info("Executando todos os seeders");
                Artisan::call('db:seed');
            }

            $this->info("✅ Seeders executados com sucesso para tenant {$tenant->id}");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return 1;
        } finally {
            Tenant::forgetCurrent();
        }
    }

    /**
     * Executar seeders para todos os tenants
     */
    private function seedAllTenants(?string $seederClass = null): int
    {
        $tenants = Tenant::active()->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant ativo encontrado.');
            return 0;
        }

        $this->info("🌱 Executando seeders para {$tenants->count()} tenants...");
        $this->newLine();

        $success = 0;
        $failed = 0;

        foreach ($tenants as $tenant) {
            $this->line("Processando tenant: {$tenant->name} ({$tenant->id})");

            try {
                if (!$tenant->schemaExists()) {
                    $this->warn("  Schema não existe. Pulando...");
                    $failed++;
                    continue;
                }

                $tenant->makeCurrent();

                if ($seederClass) {
                    $this->info("  Executando seeder: {$seederClass}");
                    Artisan::call('db:seed', ['--class' => $seederClass]);
                } else {
                    $this->info("  Executando todos os seeders");
                    Artisan::call('db:seed');
                }

                $this->info("  ✅ Sucesso");
                $success++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erro: " . $e->getMessage());
                $failed++;
            } finally {
                Tenant::forgetCurrent();
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
