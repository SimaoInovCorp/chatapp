<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_invitations', function (Blueprint $table) {
            $table->foreignId('invited_user_id')->nullable()->after('inviter_id')->constrained('users')->cascadeOnDelete();

            // Prevent duplicate pending invitations to the same user for the same room
            $table->unique(['room_id', 'invited_user_id', 'status'], 'room_invitation_unique_pending');
        });
    }

    public function down(): void
    {
        Schema::table('room_invitations', function (Blueprint $table) {
            $table->dropUnique('room_invitation_unique_pending');
            $table->dropConstrainedForeignId('invited_user_id');
        });
    }
};