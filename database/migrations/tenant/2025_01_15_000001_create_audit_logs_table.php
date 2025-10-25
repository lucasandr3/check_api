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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // created, updated, deleted, restored
            $table->string('auditable_type'); // Nome da classe do modelo
            $table->unsignedBigInteger('auditable_id'); // ID do modelo
            $table->unsignedBigInteger('user_id')->nullable(); // Usuário que executou a ação
            $table->string('user_email')->nullable(); // Email do usuário para auditoria
            $table->string('ip_address')->nullable(); // IP do usuário
            $table->string('user_agent')->nullable(); // User agent do usuário
            $table->json('old_values')->nullable(); // Valores antigos (para updates)
            $table->json('new_values')->nullable(); // Valores novos
            $table->json('changed_fields')->nullable(); // Campos que foram alterados
            $table->string('route_name')->nullable(); // Nome da rota
            $table->string('method')->nullable(); // Método HTTP
            $table->text('description')->nullable(); // Descrição da ação
            $table->json('metadata')->nullable(); // Metadados adicionais
            $table->timestamps();

            // Índices para performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id']);
            $table->index(['event_type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
