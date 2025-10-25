<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('type', ['matriz', 'filial'])->default('matriz')->after('schema_name');
            $table->string('parent_id')->nullable()->after('type');
            
            $table->foreign('parent_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['type']);
            $table->index(['parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['type', 'parent_id']);
        });
    }
};
