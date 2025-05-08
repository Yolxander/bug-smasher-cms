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
        Schema::create('qa_checklist_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qa_checklist_id')->constrained('qa_checklists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['accepted', 'rejected'])->default('accepted');
            $table->dateTime('assigned_at');
            $table->dateTime('due_date')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->unique(['qa_checklist_id', 'user_id']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_checklist_assignments');
    }
};
