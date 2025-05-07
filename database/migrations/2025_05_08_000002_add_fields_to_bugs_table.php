<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->foreignId('reported_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->dateTime('due_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bugs', function (Blueprint $table) {
            $table->dropForeign(['reported_by']);
            $table->dropForeign(['team_id']);
            $table->dropColumn(['reported_by', 'team_id', 'due_date']);
        });
    }
};
