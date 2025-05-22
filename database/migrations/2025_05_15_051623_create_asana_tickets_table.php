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
        Schema::create('asana_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->nullable()->unique();
            $table->foreignId('bug_id')->nullable()->constrained('bugs')->nullOnDelete();
            $table->foreignId('qa_checklist_id')->nullable()->constrained('qa_checklists')->nullOnDelete();
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asana_tickets');
    }
};
