<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'invited_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $roomId = (int) $this->input('room_id');
            $invitedUserId = (int) $this->input('invited_user_id');

            if (!$roomId || !$invitedUserId) {
                return;
            }

            $room = \App\Models\Room::with('users:id')->find($roomId);
            if (!$room) {
                return;
            }

            if ($room->users->pluck('id')->contains($invitedUserId)) {
                $validator->errors()->add('invited_user_id', 'User is already a member of this room.');
            }

            $pendingExists = \App\Models\RoomInvitation::query()
                ->where('room_id', $roomId)
                ->where('invited_user_id', $invitedUserId)
                ->where('status', 'pending')
                ->exists();

            if ($pendingExists) {
                $validator->errors()->add('invited_user_id', 'This user already has a pending invitation.');
            }
        });
    }
}