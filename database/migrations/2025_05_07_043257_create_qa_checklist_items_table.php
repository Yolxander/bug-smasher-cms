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
        Schema::create('qa_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('qa_checklists')->onDelete('cascade');
            $table->text('item_text');
            $table->string('item_type', 50)->default('checkbox');
            $table->boolean('is_required')->default(false);
            $table->integer('order_number');
            $table->timestamps();

            // Add indexes
            $table->index('checklist_id');
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qa_checklist_items');
    }
};
