<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar se as colunas já existem antes de adicionar
        $columns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'tenants'");
        $existingColumns = array_column($columns, 'column_name');

        Schema::table('tenants', function (Blueprint $table) use ($existingColumns) {
            // Adicionar colunas se não existirem
            if (!in_array('schema_name', $existingColumns)) {
                $table->string('schema_name')->nullable();
            }
            if (!in_array('database_name', $existingColumns)) {
                $table->string('database_name')->nullable();
            }
            if (!in_array('status', $existingColumns)) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            }
            if (!in_array('settings', $existingColumns)) {
                $table->json('settings')->nullable();
            }
        });

        // Atualizar tenants existentes para ter schema_name
        DB::statement("
            UPDATE tenants 
            SET schema_name = CONCAT('tenant_', id) 
            WHERE schema_name IS NULL
        ");

        // Tornar schema_name obrigatório e único
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('schema_name')->nullable(false)->unique()->change();
        });

        // Adicionar índices
        Schema::table('tenants', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('status', $existingColumns)) {
                $table->index(['status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['schema_name', 'database_name', 'status', 'settings']);
        });
    }
};
