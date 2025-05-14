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
            // Drop the existing foreign key constraint
            $table->dropForeign(['assignee_id']);

            // Add the new foreign key constraint referencing users table
            $table->foreign('assignee_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            // Drop the users foreign key constraint
            $table->dropForeign(['assignee_id']);

            // Add back the original profiles foreign key constraint
            $table->foreign('assignee_id')
                  ->references('id')
                  ->on('profiles')
                  ->nullOnDelete();
        });
    }
};
