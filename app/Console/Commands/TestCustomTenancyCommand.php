<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCustomTenancyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:test {tenant_id}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Testar sistema de multi-tenancy customizado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        
        $this->info("🧪 Testando sistema de multi-tenancy customizado");
        $this->info("Tenant ID: {$tenantId}");
        $this->newLine();

        // 1. Verificar se tenant existe
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("❌ Tenant {$tenantId} não encontrado");
            return 1;
        }

        $this->info("✅ Tenant encontrado: {$tenant->name}");

        // 2. Verificar schema
        if ($tenant->schemaExists()) {
            $this->info("✅ Schema {$tenant->schema_name} existe");
        } else {
            $this->error("❌ Schema {$tenant->schema_name} não existe");
            return 1;
        }

        // 3. Testar inicialização do tenant
        try {
            $tenant->makeCurrent();
            $this->info("✅ Tenant inicializado com sucesso");
        } catch (\Exception $e) {
            $this->error("❌ Erro ao inicializar tenant: " . $e->getMessage());
            return 1;
        }

        // 4. Verificar search_path
        $searchPath = DB::select("SHOW search_path")[0]->search_path;
        $this->info("📁 Search path atual: {$searchPath}");

        if (str_contains($searchPath, $tenant->schema_name)) {
            $this->info("✅ Search path configurado corretamente");
        } else {
            $this->warn("⚠️  Search path pode não estar configurado corretamente");
        }

        // 5. Listar tabelas no schema
        $tables = DB::select("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = ? 
            ORDER BY table_name
        ", [$tenant->schema_name]);

        if (!empty($tables)) {
            $tableNames = array_column($tables, 'table_name');
            $this->info("📋 Tabelas no schema ({$tenant->schema_name}):");
            foreach ($tableNames as $table) {
                $this->line("  - {$table}");
            }
        } else {
            $this->warn("⚠️  Nenhuma tabela encontrada no schema");
        }

        // 6. Verificar migrations
        if (in_array('migrations', array_column($tables, 'table_name'))) {
            $migrations = DB::select("SELECT migration, batch FROM {$tenant->schema_name}.migrations ORDER BY batch, migration");
            
            if (!empty($migrations)) {
                $this->info("📊 Migrations executadas:");
                foreach ($migrations as $migration) {
                    $this->line("  - Batch {$migration->batch}: {$migration->migration}");
                }
            } else {
                $this->warn("⚠️  Nenhuma migration registrada");
            }
        } else {
            $this->warn("⚠️  Tabela migrations não encontrada");
        }

        // 7. Resetar tenant
        Tenant::forgetCurrent();
        $this->info("🔄 Tenant resetado");

        $this->newLine();
        $this->info("✅ Teste concluído com sucesso!");
        
        return 0;
    }
}
