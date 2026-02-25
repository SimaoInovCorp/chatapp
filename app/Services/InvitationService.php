<?php

namespace App\Services;

use App\Mail\RoomInvitationMail;
use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function create(Room $room, User $inviter, string $email, ?string $expiresAt = null): RoomInvitation
    {
        $invitation = RoomInvitation::create([
            'room_id' => $room->id,
            'inviter_id' => $inviter->id,
            'invited_email' => $email,
            'token' => Str::uuid()->toString(),
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        Mail::to($email)->send(new RoomInvitationMail($invitation));

        return $invitation;
    }

    public function accept(RoomInvitation $invitation, User $user): RoomInvitation
    {
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $invitation->room->users()->syncWithoutDetaching([$user->id]);

        return $invitation;
    }
}