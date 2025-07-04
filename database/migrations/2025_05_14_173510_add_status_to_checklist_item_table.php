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
        Schema::table('qa_checklist_items', function (Blueprint $table) {
            $table->enum('status', ['passed', 'failed', 'pending'])->default('pending')->after('answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qa_checklist_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
