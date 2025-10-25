<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('office_id');
            $table->morphs('maintainable'); // vehicle_id or equipment_id
            $table->unsignedBigInteger('schedule_id')->nullable(); // Reference to maintenance schedule
            $table->enum('type', ['preventive', 'corrective', 'routine'])->default('preventive');
            $table->text('description');
            $table->json('parts_used')->nullable(); // Parts and quantities used
            $table->decimal('labor_hours', 5, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->unsignedBigInteger('performed_by'); // User who performed maintenance
            $table->datetime('performed_at');
            $table->date('next_maintenance_date')->nullable();
            $table->integer('next_maintenance_km')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('completed');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('maintenance_schedules')->onDelete('set null');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('restrict');
            
            $table->index(['tenant_id', 'office_id']);
            $table->index(['performed_at', 'type']);
            $table->index(['status', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
