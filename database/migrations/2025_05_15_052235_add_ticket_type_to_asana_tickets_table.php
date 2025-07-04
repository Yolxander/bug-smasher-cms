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
        Schema::table('asana_tickets', function (Blueprint $table) {
            $table->string('ticket_type')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asana_tickets', function (Blueprint $table) {
            $table->dropColumn('ticket_type');
        });
    }
};
