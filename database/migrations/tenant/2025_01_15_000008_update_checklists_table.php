<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            // Add new columns
            $table->unsignedBigInteger('template_id')->nullable()->after('company_id');
            $table->string('checklistable_type')->nullable()->after('template_id');
            $table->unsignedBigInteger('checklistable_id')->nullable()->after('checklistable_type');
            $table->enum('type', ['preventive', 'routine', 'corrective'])->default('routine')->after('checklistable_id');
            $table->datetime('started_at')->nullable()->after('status');
            $table->datetime('completed_at')->nullable()->after('started_at');
            
            // Add foreign key for template
            $table->foreign('template_id')->references('id')->on('checklist_templates')->onDelete('set null');
            
            // Add indexes
            $table->index(['checklistable_type', 'checklistable_id']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropIndex(['checklistable_type', 'checklistable_id']);
            $table->dropIndex(['type', 'status']);
            
            $table->dropColumn([
                'template_id',
                'checklistable_type',
                'checklistable_id',
                'type',
                'started_at',
                'completed_at'
            ]);
        });
    }
};
