<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->string('email');
            $table->foreignId('invited_by')->constrained('users');
            $table->enum('role', ['admin', 'member', 'viewer'])->default('member');
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->string('invitation_token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['team_id', 'email']);
            $table->index('status');
            $table->index('invitation_token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};
