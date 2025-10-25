<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('company_id');
            $table->morphs('maintainable'); // vehicle_id or equipment_id
            $table->enum('type', ['preventive', 'corrective'])->default('preventive');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('frequency_type', ['days', 'km', 'hours'])->default('days');
            $table->integer('frequency_value');
            $table->date('last_performed_at')->nullable();
            $table->integer('last_performed_km')->nullable();
            $table->date('next_due_date')->nullable();
            $table->integer('next_due_km')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('estimated_hours', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->index(['tenant_id', 'company_id']);
            $table->index(['next_due_date', 'is_active']);
            $table->index(['priority', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
