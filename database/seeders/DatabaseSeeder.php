<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Room;
use App\Models\Message;
use App\Models\RoomInvitation;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'spmmazb@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'status' => UserStatus::Online,
                'email_verified_at' => now(),
            ],
        );

        $guest = User::updateOrCreate(
            ['email' => 'simao_morais@msn.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'role' => UserRole::User,
                'status' => UserStatus::Online,
                'email_verified_at' => now(),
            ],
        );

        $testRoom = Room::firstOrCreate(
            ['name' => 'Test Room'],
            [
                'created_by' => $admin->id,
            ],
        );

        $internalRoom = Room::firstOrCreate(
            ['name' => 'InovCorp internal chat'],
            [
                'created_by' => $admin->id,
            ],
        );

        $testRoom->users()->syncWithoutDetaching([$admin->id, $guest->id]);
        $internalRoom->users()->syncWithoutDetaching([$admin->id, $guest->id]);

        $testRoom->messages()->create([
            'user_id' => $admin->id,
            'body' => 'Welcome to the Test Room! Feel free to try replies here.',
        ]);

        $testRoom->messages()->create([
            'user_id' => $guest->id,
            'body' => 'Hi Admin, Guest here. Messages look good!',
        ]);

        $internalRoom->messages()->create([
            'user_id' => $admin->id,
            'body' => 'Welcome to InovCorp internal chat. Use this for team threads.',
        ]);

        $internalRoom->messages()->create([
            'user_id' => $guest->id,
            'body' => 'Copy that, I will keep updates here.',
        ]);

        $guest->messages()->create([
            'user_id' => $admin->id,
            'body' => 'DM from Admin to Guest — testing direct messages.',
        ]);

        $admin->messages()->create([
            'user_id' => $guest->id,
            'body' => 'Reply from Guest back to Admin — DM flow works.',
        ]);

        $pendingUser = User::updateOrCreate(
            ['email' => 'pending@inovcorp.test'],
            [
                'name' => 'Pending User',
                'password' => Hash::make('password'),
                'role' => UserRole::User,
                'status' => UserStatus::Offline,
                'email_verified_at' => now(),
            ],
        );

        RoomInvitation::create([
            'room_id' => $testRoom->id,
            'inviter_id' => $admin->id,
            'invited_user_id' => $pendingUser->id,
            'invited_email' => $pendingUser->email,
            'token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'pending',
        ]);
    }
}
