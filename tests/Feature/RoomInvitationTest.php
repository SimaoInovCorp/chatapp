<?php

use App\Enums\UserRole;
use App\Models\Room;
use App\Models\RoomInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function makeUser(string $name, string $email, UserRole $role = UserRole::User): User
{
    return User::factory()->create([
        'name' => $name,
        'email' => $email,
        'role' => $role,
    ]);
}

function makeRoom(User $creator, string $name = 'Room Alpha'): Room
{
    $room = Room::create([
        'name' => $name,
        'created_by' => $creator->id,
    ]);
    $room->users()->syncWithoutDetaching([$creator->id]);

    return $room;
}

it('allows admin to create in-app invitation for a user not in the room', function () {
    $admin = makeUser('Admin', 'admin@example.com', UserRole::Admin);
    $member = makeUser('Member', 'member@example.com');
    $room = makeRoom($admin);

    actingAs($admin)
        ->post(route('rooms.invitations.store'), [
            'room_id' => $room->id,
            'invited_user_id' => $member->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('room_invitations', [
        'room_id' => $room->id,
        'invited_user_id' => $member->id,
        'status' => 'pending',
    ]);
});

it('blocks inviting a user already in the room', function () {
    $admin = makeUser('Admin', 'admin@example.com', UserRole::Admin);
    $member = makeUser('Member', 'member@example.com');
    $room = makeRoom($admin);
    $room->users()->syncWithoutDetaching([$member->id]);

    actingAs($admin)
        ->post(route('rooms.invitations.store'), [
            'room_id' => $room->id,
            'invited_user_id' => $member->id,
        ])
        ->assertSessionHasErrors('invited_user_id');
});

it('allows the invited user to accept and join the room', function () {
    $admin = makeUser('Admin', 'admin@example.com', UserRole::Admin);
    $member = makeUser('Member', 'member@example.com');
    $room = makeRoom($admin);

    $invitation = RoomInvitation::create([
        'room_id' => $room->id,
        'inviter_id' => $admin->id,
        'invited_user_id' => $member->id,
        'invited_email' => $member->email,
        'token' => 'token-123',
        'status' => 'pending',
    ]);

    actingAs($member)
        ->post(route('invitations.accept', $invitation))
        ->assertRedirect(route('chat'));

    expect($invitation->fresh()->status)->toBe('accepted');
    expect($room->users()->whereKey($member->id)->exists())->toBeTrue();
});

it('allows the invited user to decline', function () {
    $admin = makeUser('Admin', 'admin@example.com', UserRole::Admin);
    $member = makeUser('Member', 'member@example.com');
    $room = makeRoom($admin);

    $invitation = RoomInvitation::create([
        'room_id' => $room->id,
        'inviter_id' => $admin->id,
        'invited_user_id' => $member->id,
        'invited_email' => $member->email,
        'token' => 'token-123',
        'status' => 'pending',
    ]);

    actingAs($member)
        ->post(route('invitations.decline', $invitation))
        ->assertRedirect();

    expect($invitation->fresh()->status)->toBe('declined');
});

it('allows members to leave a room', function () {
    $user = makeUser('User', 'user@example.com');
    $room = makeRoom($user);

    actingAs($user)
        ->post(route('rooms.leave', $room))
        ->assertRedirect();

    expect($room->users()->whereKey($user->id)->exists())->toBeFalse();
});