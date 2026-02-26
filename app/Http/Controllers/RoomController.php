<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $room = Room::create([
            'name' => $request->string('name'),
            'avatar' => $request->input('avatar'),
            'created_by' => $request->user()->id,
        ]);

        $room->users()->syncWithoutDetaching([$request->user()->id]);

        return back()->with('status', 'Room created');
    }

    public function leave(Room $room): RedirectResponse
    {
        $user = Auth::user();

        if (!$room->users()->whereKey($user->id)->exists()) {
            abort(403, 'You are not a member of this room.');
        }

        $room->users()->detach($user->id);

        return back()->with('status', 'You left the room');
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        Gate::authorize('update', $room);

        $data = [];
        if ($request->filled('name')) {
            $data['name'] = $request->string('name')->toString();
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('room-avatars', 'public');
            $data['avatar'] = Storage::url($path);
        }

        if (!empty($data)) {
            $room->update($data);
        }

        return back()->with('status', 'Room updated');
    }

    public function destroy(Room $room): RedirectResponse
    {
        Gate::authorize('delete', $room);

        $room->users()->detach();
        $room->delete();

        return redirect()->route('chat')->with('status', 'Room deleted');
    }
}