<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                            {id : ID do tenant (ex: 1000)} 
                            {name : Nome do tenant} 
                            {--domain= : Domínio (opcional - para compatibilidade)}
                            {--run-migrations : Executar migrations automaticamente}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Criar um novo tenant com schema PostgreSQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $name = $this->argument('name');
        $domain = $this->option('domain'); // Opcional - não criar se não fornecido
        $runMigrations = $this->option('run-migrations');

        // Verificar se já existe
        if (Tenant::find($id)) {
            $this->error("Tenant com ID '{$id}' já existe!");
            return 1;
        }

        try {
            DB::beginTransaction();

            // Criar tenant
            $tenant = new Tenant([
                'id' => $id,
                'schema_name' => "tenant_{$id}",
                'status' => 'active',
                'data' => [
                    'name' => $name,
                ],
                'settings' => [
                    'created_by' => 'artisan',
                    'created_at' => now()->toISOString(),
                ]
            ]);
            $tenant->save();

            // Criar domínio apenas se fornecido
            if ($domain) {
                TenantDomain::create([
                    'tenant_id' => $tenant->id,
                    'domain' => $domain,
                    'is_primary' => true,
                ]);
            }

            // Criar schema no PostgreSQL
            if (!$tenant->createSchema()) {
                throw new \Exception("Falha ao criar schema PostgreSQL");
            }

            DB::commit();

            $this->info("✅ Tenant criado com sucesso!");
            $tableData = [
                ['ID', $tenant->id],
                ['Nome', $tenant->name],
                ['Schema', $tenant->schema_name],
                ['Status', $tenant->status],
            ];
            
            if ($domain) {
                $tableData[] = ['Domínio', $domain];
            }
            
            $this->table(['Campo', 'Valor'], $tableData);

            // Executar migrations se solicitado
            if ($runMigrations) {
                $this->info("🔄 Executando migrations...");
                if ($tenant->runMigrations()) {
                    $this->info("✅ Migrations executadas com sucesso!");
                } else {
                    $this->error("❌ Erro ao executar migrations");
                }
            } else {
                $this->info("💡 Para executar migrations: php artisan tenant:migrate {$id}");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erro ao criar tenant: " . $e->getMessage());
            return 1;
        }
    }
}