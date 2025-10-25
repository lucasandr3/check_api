<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:clean 
                            {--days=90 : Número de dias para manter os logs}
                            {--dry-run : Apenas mostrar o que seria removido, sem executar}
                            {--force : Forçar execução sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa logs de auditoria antigos para liberar espaço no banco';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $cutoffDate = now()->subDays($days);
        
        // Contar logs que seriam removidos
        $logsToDelete = AuditLog::where('created_at', '<', $cutoffDate)->count();
        
        if ($logsToDelete === 0) {
            $this->info("✅ Nenhum log antigo encontrado para remoção.");
            return 0;
        }

        $this->info("📊 Logs de auditoria que serão removidos:");
        $this->info("- Total de logs: {$logsToDelete}");
        $this->info("- Data de corte: {$cutoffDate->format('d/m/Y H:i:s')}");
        $this->info("- Logs mais antigos que: {$days} dias");

        // Mostrar estatísticas por tipo de evento
        $statsByEvent = AuditLog::where('created_at', '<', $cutoffDate)
            ->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->get();

        if ($statsByEvent->isNotEmpty()) {
            $this->info("\n📈 Distribuição por tipo de evento:");
            foreach ($statsByEvent as $stat) {
                $this->info("  - {$stat->event_type}: {$stat->count}");
            }
        }

        // Mostrar estatísticas por modelo
        $statsByModel = AuditLog::where('created_at', '<', $cutoffDate)
            ->select('auditable_type', DB::raw('count(*) as count'))
            ->groupBy('auditable_type')
            ->get();

        if ($statsByModel->isNotEmpty()) {
            $this->info("\n🏗️  Distribuição por modelo:");
            foreach ($statsByModel as $stat) {
                $modelName = class_basename($stat->auditable_type);
                $this->info("  - {$modelName}: {$stat->count}");
            }
        }

        if ($dryRun) {
            $this->info("\n🔍 Modo dry-run ativado. Nenhum log foi removido.");
            return 0;
        }

        if (!$force) {
            if (!$this->confirm("⚠️  Tem certeza que deseja remover {$logsToDelete} logs de auditoria?")) {
                $this->info("❌ Operação cancelada pelo usuário.");
                return 1;
            }
        }

        $this->info("\n🗑️  Removendo logs antigos...");

        try {
            // Remover logs em lotes para evitar timeout
            $deleted = 0;
            $batchSize = 1000;
            
            while (true) {
                $batch = AuditLog::where('created_at', '<', $cutoffDate)
                    ->limit($batchSize)
                    ->get(['id']);
                
                if ($batch->isEmpty()) {
                    break;
                }
                
                $ids = $batch->pluck('id')->toArray();
                AuditLog::whereIn('id', $ids)->delete();
                
                $deleted += count($ids);
                $this->info("  - Removidos {$deleted} logs...");
                
                // Pequena pausa para não sobrecarregar o banco
                usleep(100000); // 0.1 segundo
            }

            $this->info("✅ Sucesso! {$deleted} logs de auditoria foram removidos.");
            
            // Mostrar espaço liberado (aproximado)
            $this->info("💾 Espaço aproximado liberado: " . $this->formatBytes($deleted * 1024)); // Estimativa de 1KB por log
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erro ao remover logs de auditoria: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Formatar bytes para formato legível
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
