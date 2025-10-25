<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAcl extends Command
{
    protected $signature = 'acl:reset';
    protected $description = 'Reseta o sistema de ACL (limpa e recria todas as tabelas)';

    public function handle()
    {
        if (!$this->confirm('⚠️  ATENÇÃO: Isso irá apagar TODOS os dados do sistema de ACL. Continuar?')) {
            $this->info('❌ Operação cancelada.');
            return;
        }

        $this->info('🔄 Resetando sistema de ACL...');

        try {
            // Desabilitar verificação de chaves estrangeiras
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Limpar tabelas relacionadas ao ACL
            $tables = [
                'menu_role',
                'permission_role', 
                'role_user',
                'menus',
                'permissions',
                'roles'
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✅ Tabela {$table} limpa");
                }
            }

            // Reabilitar verificação de chaves estrangeiras
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Sistema de ACL resetado com sucesso!');
            $this->info('💡 Execute "php artisan db:seed" para recriar os dados.');

        } catch (\Exception $e) {
            $this->error('❌ Erro ao resetar sistema de ACL: ' . $e->getMessage());
        }
    }
}
