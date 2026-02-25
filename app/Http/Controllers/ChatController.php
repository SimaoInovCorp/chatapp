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

        $rooms = $user->rooms()
            ->with('creator')
            ->withCount('users')
            ->get();

        $directUsers = User::query()
            ->whereKeyNot($user->getAuthIdentifier())
            ->select(['id', 'name', 'email', 'avatar', 'status'])
            ->orderBy('name')
            ->get();

        return view('chat.index', [
            'rooms' => $rooms,
            'directUsers' => $directUsers,
            'authUser' => $user,
        ]);
    }
}