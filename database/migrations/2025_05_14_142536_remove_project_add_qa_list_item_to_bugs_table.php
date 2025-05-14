<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropColumn('project');
            $table->foreignId('qa_list_item_id')->nullable()->constrained('qa_checklist_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->jsonb('project')->default('{"id": "1", "name": "Clever Project"}');
            $table->dropForeign(['qa_list_item_id']);
            $table->dropColumn('qa_list_item_id');
        });
    }
};
