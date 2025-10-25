<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ResetProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset completo do projeto - limpa tudo e recria do zero';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Resetando projeto completamente...');
        
        // 1. Dropar todos os schemas de tenant
        $this->dropTenantSchemas();
        
        // 2. Limpar migrations
        $this->call('migrate:fresh');
        
        // 3. Executar setup completo
        $this->call('setup:project');
        
        $this->info('✅ Reset completo finalizado!');
    }
    
    /**
     * Dropar todos os schemas de tenant
     */
    private function dropTenantSchemas(): void
    {
        $this->info('🗑️ Removendo schemas de tenants...');
        
        try {
            // Buscar todos os schemas de tenant
            $schemas = DB::select("
                SELECT schema_name 
                FROM information_schema.schemata 
                WHERE schema_name LIKE 'tenant_%'
            ");
            
            foreach ($schemas as $schema) {
                $schemaName = $schema->schema_name;
                DB::statement("DROP SCHEMA IF EXISTS {$schemaName} CASCADE");
                $this->line("  ✅ Schema removido: {$schemaName}");
            }
            
        } catch (\Exception $e) {
            $this->warn("  ⚠️ Erro ao remover schemas: " . $e->getMessage());
        }
    }
}
