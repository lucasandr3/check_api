<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders de teste...');
        $this->command->info('');

        // 1. Criar tenants
        $this->call(TenantTestSeeder::class);
        $this->command->info('');

        // 2. Criar usuários
        $this->call(UserTestSeeder::class);
        $this->command->info('');

        // 3. Criar dados dos tenants (menus, veículos, etc.)
        $this->call(TenantDataSeeder::class);
        $this->command->info('');

        $this->command->info('🎉 Seeders de teste concluídos!');
        $this->command->info('');
        $this->command->info('📝 Próximos passos:');
        $this->command->info('  1. Testar login com as credenciais acima');
        $this->command->info('  2. Usar header X-Account-ID para identificar tenant');
        $this->command->info('  3. Testar isolamento de dados entre tenants');
    }
}
