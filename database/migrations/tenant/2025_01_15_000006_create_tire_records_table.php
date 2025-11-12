<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tire_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->enum('tire_position', ['front_left', 'front_right', 'rear_left', 'rear_right', 'spare']);
            $table->string('tire_brand');
            $table->string('tire_model');
            $table->string('tire_size'); // ex: 195/65R15
            $table->date('installation_date');
            $table->integer('installation_km')->nullable();
            $table->date('removal_date')->nullable();
            $table->integer('removal_km')->nullable();
            $table->enum('removal_reason', ['wear', 'damage', 'rotation', 'replacement'])->nullable();
            $table->decimal('tread_depth_new', 4, 2)->nullable(); // in mm
            $table->decimal('tread_depth_removal', 4, 2)->nullable(); // in mm
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('warranty_km')->nullable();
            $table->enum('status', ['active', 'removed', 'rotated'])->default('active');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            
            $table->index(['tenant_id', 'company_id']);
            $table->index(['vehicle_id', 'tire_position', 'status']);
            $table->index(['installation_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_records');
    }
};
