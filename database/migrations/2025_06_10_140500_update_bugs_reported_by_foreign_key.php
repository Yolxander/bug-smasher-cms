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
            $table->dropForeign(['reported_by']);

            // Add the new foreign key constraint referencing users table
            $table->foreign('reported_by')
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
            $table->dropForeign(['reported_by']);

            // Add back the original profiles foreign key constraint
            $table->foreign('reported_by')
                  ->references('id')
                  ->on('profiles')
                  ->nullOnDelete();
        });
    }
};
