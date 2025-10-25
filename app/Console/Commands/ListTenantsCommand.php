<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class ListTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list {--status= : Filtrar por status}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Listar todos os tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $status = $this->option('status');
        
        $query = Tenant::with('domains');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $tenants = $query->orderBy('id')->get();

        if ($tenants->isEmpty()) {
            $this->info('Nenhum tenant encontrado.');
            return 0;
        }

        $this->info("📋 Lista de Tenants:");
        $this->newLine();

        $tableData = [];
        foreach ($tenants as $tenant) {
            $domains = $tenant->domains->pluck('domain')->implode(', ');
            $schemaExists = $tenant->schemaExists() ? '✅ Sim' : '❌ Não';
            
            $tableData[] = [
                $tenant->id,
                $tenant->name,
                $tenant->status,
                $domains ?: 'Nenhum',
                $tenant->schema_name,
                $schemaExists,
                $tenant->created_at->format('d/m/Y H:i'),
            ];
        }

        $this->table([
            'ID',
            'Nome',
            'Status',
            'Domínios',
            'Schema',
            'Schema Existe',
            'Criado em'
        ], $tableData);

        $this->newLine();
        $this->info("Total de tenants: " . $tenants->count());
        
        return 0;
    }
}