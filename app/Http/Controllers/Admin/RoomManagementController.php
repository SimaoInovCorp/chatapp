<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomManagementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->role !== UserRole::Admin) {
            abort(403);
        }

        $rooms = Room::query()
            ->with(['users:id,name,email'])
            ->withCount('users')
            ->orderByDesc('created_at')
            ->paginate(10);

        $allUsers = User::query()
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        $roomInviteOptions = $rooms->getCollection()->mapWithKeys(function (Room $room) use ($allUsers) {
            $currentIds = $room->users->pluck('id');
            $options = $allUsers->reject(fn ($candidate) => $currentIds->contains($candidate->id))->values();
            return [$room->id => $options];
        });

        return view('admin.rooms.index', [
            'rooms' => $rooms,
            'roomInviteOptions' => $roomInviteOptions,
        ]);
    }
}
