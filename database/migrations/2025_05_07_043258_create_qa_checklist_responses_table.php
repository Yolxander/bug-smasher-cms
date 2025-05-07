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
        Schema::create('qa_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('qa_checklists')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('qa_checklist_items')->onDelete('cascade');
            $table->text('response');
            $table->foreignId('responded_by')->constrained('users');
            $table->dateTime('responded_at');
            $table->string('status', 50)->default('pending');
            $table->timestamps();

            // Add indexes
            $table->index('checklist_id');
            $table->index('item_id');
            $table->index('responded_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_checklist_responses');
    }
};
