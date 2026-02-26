<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var Authenticatable&User $user */
        $user = $request->user();

        $roomsPaginator = $user->rooms()
            ->with(['creator', 'users:id'])
            ->withCount('users')
            ->paginate(10);

        $directUsersPaginator = User::query()
            ->whereKeyNot($user->getAuthIdentifier())
            ->select(['id', 'name', 'email', 'avatar', 'status'])
            ->orderBy('name')
            ->paginate(12);

        $rooms = collect($roomsPaginator->items());
        $directUsers = collect($directUsersPaginator->items());

        $roomsAll = $user->rooms()->with('users:id')->withCount('users')->get();
        $directUsersAll = User::query()
            ->whereKeyNot($user->getAuthIdentifier())
            ->select(['id', 'name', 'email', 'avatar', 'status'])
            ->orderBy('name')
            ->get();

        $roomInviteOptions = $roomsAll->mapWithKeys(function (Room $room) use ($directUsersAll) {
            $currentIds = $room->users->pluck('id');
            $options = $directUsersAll->reject(fn ($candidate) => $currentIds->contains($candidate->id))->values();
            return [$room->id => $options];
        });

        $invitations = \App\Models\RoomInvitation::query()
            ->with(['room', 'inviter'])
            ->where('invited_user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('chat.index', [
            'rooms' => $rooms,
            'roomsPaginator' => $roomsPaginator,
            'roomsAll' => $roomsAll,
            'directUsers' => $directUsers,
            'directUsersPaginator' => $directUsersPaginator,
            'authUser' => $user,
            'roomInviteOptions' => $roomInviteOptions,
            'invitations' => $invitations,
        ]);
    }
}