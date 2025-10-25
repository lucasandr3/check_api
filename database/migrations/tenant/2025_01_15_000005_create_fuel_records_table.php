<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->enum('fuel_type', ['gasoline', 'ethanol', 'diesel', 'flex'])->default('gasoline');
            $table->decimal('liters', 8, 3);
            $table->decimal('price_per_liter', 6, 3);
            $table->decimal('total_cost', 10, 2);
            $table->integer('odometer_reading');
            $table->string('fuel_station')->nullable();
            $table->string('driver_name')->nullable();
            $table->datetime('fuel_date');
            $table->text('observations')->nullable();
            $table->string('receipt_photo')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            
            $table->index(['tenant_id', 'office_id']);
            $table->index(['vehicle_id', 'fuel_date']);
            $table->index(['fuel_date', 'fuel_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};
