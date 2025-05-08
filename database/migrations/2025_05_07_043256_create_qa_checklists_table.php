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
        Schema::create('qa_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->integer('version')->default(1);
            $table->foreignId('category_id')->nullable()->constrained('qa_checklist_categories')->nullOnDelete();
            $table->dateTime('due_date')->nullable();
            $table->string('priority', 50)->nullable();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            // Add indexes
            $table->index('status');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_checklists');
    }
};
