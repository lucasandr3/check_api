<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete 
                            {tenant : ID do tenant} 
                            {--force : Forçar exclusão sem confirmação}
                            {--keep-schema : Manter schema no banco}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Deletar um tenant e seu schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $force = $this->option('force');
        $keepSchema = $this->option('keep-schema');

        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' não encontrado");
            return 1;
        }

        // Mostrar informações do tenant
        $this->warn("⚠️  Você está prestes a deletar o tenant:");
        $this->table(['Campo', 'Valor'], [
            ['ID', $tenant->id],
            ['Nome', $tenant->name],
            ['Schema', $tenant->schema_name],
            ['Status', $tenant->status],
            ['Criado em', $tenant->created_at->format('d/m/Y H:i')],
        ]);

        // Confirmação
        if (!$force) {
            if (!$this->confirm('Tem certeza que deseja deletar este tenant?')) {
                $this->info('Operação cancelada.');
                return 0;
            }

            if (!$keepSchema && $tenant->schemaExists()) {
                if (!$this->confirm('Isso também deletará o schema e TODOS OS DADOS. Continuar?')) {
                    $this->info('Operação cancelada.');
                    return 0;
                }
            }
        }

        try {
            DB::beginTransaction();

            // Deletar schema se solicitado
            if (!$keepSchema && $tenant->schemaExists()) {
                $this->info("🗑️  Deletando schema...");
                if (!$tenant->deleteSchema()) {
                    throw new \Exception("Falha ao deletar schema PostgreSQL");
                }
                $this->info("✅ Schema deletado");
            }

            // Deletar tenant (cascade deletará domínios)
            $tenant->delete();

            DB::commit();

            $this->info("✅ Tenant deletado com sucesso!");
            
            if ($keepSchema) {
                $this->warn("⚠️  Schema '{$tenant->schema_name}' foi mantido no banco de dados");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erro ao deletar tenant: " . $e->getMessage());
            return 1;
        }
    }
}