<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\StoreRoomInvitationRequest;
use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoomInvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        $invitations = RoomInvitation::query()
            ->with(['room', 'inviter'])
            ->where('invited_user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('invitations.index', [
            'invitations' => $invitations,
        ]);
    }

    public function show(string $token)
    {
        $invitation = RoomInvitation::with(['room', 'inviter'])->where('token', $token)->firstOrFail();

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }

    public function store(StoreRoomInvitationRequest $request): RedirectResponse
    {
        $room = Room::findOrFail($request->integer('room_id'));

        Gate::authorize('invite', $room);

        $invitee = User::findOrFail($request->integer('invited_user_id'));

        $this->invitationService->create(
            $room,
            $request->user(),
            $invitee,
        );

        return back()->with('status', 'Invitation created');
    }

    public function accept(AcceptInvitationRequest $request, RoomInvitation $invitation): RedirectResponse
    {
        $this->authorizeInvitation($invitation, $request->user());

        $this->invitationService->accept($invitation, $request->user());

        return redirect()->route('chat')->with('status', 'Invitation accepted');
    }

    public function decline(AcceptInvitationRequest $request, RoomInvitation $invitation): RedirectResponse
    {
        $this->authorizeInvitation($invitation, $request->user());

        $this->invitationService->decline($invitation, $request->user());

        return back()->with('status', 'Invitation declined');
    }

    private function authorizeInvitation(RoomInvitation $invitation, $user): void
    {
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(403, 'This invitation has expired.');
        }

        if ($invitation->invited_user_id !== $user->id) {
            abort(403, 'This invitation is not for you.');
        }
    }
}