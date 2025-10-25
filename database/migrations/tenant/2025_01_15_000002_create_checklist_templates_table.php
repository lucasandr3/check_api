<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('office_id');
            $table->string('name');
            $table->enum('type', ['preventive', 'routine', 'corrective'])->default('routine');
            $table->enum('category', ['vehicle', 'equipment'])->default('vehicle');
            $table->json('items'); // Template items structure
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            
            $table->index(['tenant_id', 'office_id']);
            $table->index(['type', 'category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
