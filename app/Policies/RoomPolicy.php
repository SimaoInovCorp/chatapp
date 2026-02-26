<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($room->relationLoaded('users')) {
            return $room->users->contains($user->id);
        }

        return $room->users()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function invite(User $user, Room $room): bool
    {
        return $user->role === UserRole::Admin || $room->created_by === $user->id;
    }

    public function sendMessage(User $user, Room $room): bool
    {
        return $this->view($user, $room);
    }

    public function update(User $user, Room $room): bool
    {
        return $user->role === UserRole::Admin || $room->created_by === $user->id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->role === UserRole::Admin || $room->created_by === $user->id;
    }
}