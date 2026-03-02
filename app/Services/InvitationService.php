<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvitationService
{
    public function create(Room $room, User $inviter, User $invitee): RoomInvitation
    {
        // Avoid duplicates when status is pending
        $existing = RoomInvitation::query()
            ->where('room_id', $room->id)
            ->where('invited_user_id', $invitee->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        $invitation = RoomInvitation::create([
            'room_id' => $room->id,
            'inviter_id' => $inviter->id,
            'invited_user_id' => $invitee->id,
            'invited_email' => $invitee->email,
            'token' => Str::uuid()->toString(),
            'status' => 'pending',
            'expires_at' => null,
        ]);

        return $invitation;
    }

    public function accept(RoomInvitation $invitation, User $user): RoomInvitation
    {
        if ($invitation->invited_user_id !== $user->id) {
            abort(403, 'You cannot accept an invitation not addressed to you.');
        }

        if ($invitation->status !== 'pending') {
            return $invitation;
        }

        DB::transaction(function () use ($invitation, $user) {
            // Archive any previous accepted invitations for this room/user to avoid unique constraint issues
            RoomInvitation::query()
                ->where('room_id', $invitation->room_id)
                ->where('invited_user_id', $invitation->invited_user_id)
                ->where('status', 'accepted')
                ->where('id', '!=', $invitation->id)
                ->update([
                    'status' => 'archived',
                ]);

            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $invitation->room->users()->syncWithoutDetaching([$user->id]);
        });

        return $invitation->refresh();
    }

    public function decline(RoomInvitation $invitation, User $user): RoomInvitation
    {
        if ($invitation->invited_user_id !== $user->id) {
            abort(403, 'You cannot decline an invitation not addressed to you.');
        }

        if ($invitation->status !== 'pending') {
            return $invitation;
        }

        $invitation->update([
            'status' => 'declined',
            'accepted_at' => null,
        ]);

        return $invitation;
    }
}