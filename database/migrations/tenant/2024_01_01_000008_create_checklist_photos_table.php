<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_photos', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // Usando string para compatibilidade com tenancy
            $table->unsignedBigInteger('checklist_id');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->integer('size');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_photos');
    }
};
