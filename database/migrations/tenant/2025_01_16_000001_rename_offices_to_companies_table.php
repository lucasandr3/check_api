<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Se a tabela offices existe, renomear para companies
        if (Schema::hasTable('offices')) {
            Schema::rename('offices', 'companies');
        } else {
            // Se não existe, criar a tabela companies diretamente
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id'); // Usando string para compatibilidade com tenancy
                $table->string('name');
                $table->text('address');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->string('cnpj')->nullable();
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
        
        // Adicionar coluna cnpj se não existir
        if (Schema::hasTable('companies') && !Schema::hasColumn('companies', 'cnpj')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('cnpj')->nullable()->after('email');
            });
        }
        
        // Renomear todas as colunas office_id para company_id
        $tables = [
            'users',
            'vehicles', 
            'clients',
            'equipment',
            'checklist_templates',
            'maintenance_schedules',
            'maintenance_records',
            'fuel_records',
            'tire_records',
            'checklists',
            'services'
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'office_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('office_id', 'company_id');
                });
            }
        }
        
        // Recriar as foreign keys com os novos nomes
        $this->recreateForeignKeys();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter as foreign keys
        $tables = [
            'users',
            'vehicles', 
            'clients',
            'equipment',
            'checklist_templates',
            'maintenance_schedules',
            'maintenance_records',
            'fuel_records',
            'tire_records',
            'checklists',
            'services'
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['company_id']);
                });
            }
        }
        
        // Renomear colunas de volta
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('company_id', 'office_id');
                });
            }
        }
        
        // Renomear tabela de volta
        if (Schema::hasTable('companies')) {
            Schema::rename('companies', 'offices');
        }
        
        // Recriar foreign keys originais
        $this->recreateForeignKeys('offices');
    }

    /**
     * Recriar foreign keys
     */
    private function recreateForeignKeys($tableName = 'companies'): void
    {
        $tables = [
            'users',
            'vehicles', 
            'clients',
            'equipment',
            'checklist_templates',
            'maintenance_schedules',
            'maintenance_records',
            'fuel_records',
            'tire_records',
            'checklists',
            'services'
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $columnName = $tableName === 'companies' ? 'company_id' : 'office_id';
                
                if (Schema::hasColumn($table, $columnName)) {
                    Schema::table($table, function (Blueprint $table) use ($tableName, $columnName) {
                        $table->foreign($columnName)->references('id')->on($tableName)->onDelete('cascade');
                    });
                }
            }
        }
    }
};
