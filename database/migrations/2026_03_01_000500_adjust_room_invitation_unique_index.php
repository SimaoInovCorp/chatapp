<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS room_invitation_unique_pending');

        // Use a partial unique index so only pending invitations must be unique per room/user
        DB::statement('CREATE UNIQUE INDEX room_invitation_unique_pending ON room_invitations (room_id, invited_user_id) WHERE status = "pending"');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS room_invitation_unique_pending');

        Schema::table('room_invitations', function (Blueprint $table) {
            $table->unique(['room_id', 'invited_user_id', 'status'], 'room_invitation_unique_pending');
        });
    }
};