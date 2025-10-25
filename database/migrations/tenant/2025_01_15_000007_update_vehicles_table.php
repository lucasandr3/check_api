<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('fuel_type', ['gasoline', 'ethanol', 'diesel', 'flex'])->default('gasoline')->after('chassis');
            $table->string('engine')->nullable()->after('fuel_type');
            $table->enum('transmission', ['manual', 'automatic'])->default('manual')->after('engine');
            $table->enum('category', ['car', 'truck', 'motorcycle', 'van'])->default('car')->after('transmission');
            $table->integer('current_km')->default(0)->after('category');
            $table->date('acquisition_date')->nullable()->after('current_km');
            $table->date('license_expiration')->nullable()->after('acquisition_date');
            $table->date('insurance_expiration')->nullable()->after('license_expiration');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active')->after('insurance_expiration');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'fuel_type',
                'engine',
                'transmission',
                'category',
                'current_km',
                'acquisition_date',
                'license_expiration',
                'insurance_expiration',
                'status'
            ]);
        });
    }
};
