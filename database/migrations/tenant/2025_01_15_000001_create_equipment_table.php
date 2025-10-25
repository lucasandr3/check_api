<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->string('type'); // 'generator', 'compressor', 'crane', etc.
            $table->string('brand');
            $table->string('model');
            $table->string('serial_number')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->date('warranty_expiration')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive', 'repair'])->default('active');
            $table->string('location')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            
            $table->index(['tenant_id', 'office_id']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
