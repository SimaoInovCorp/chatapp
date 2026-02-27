<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return redirect()->route('chat');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', ChatController::class)->name('chat');

    Route::get('/invitations', [RoomInvitationController::class, 'index'])->name('invitations.index');

    Route::get('/rooms', function () {
        return redirect()->route('chat');
    })->name('rooms.index');

    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::patch('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');

    Route::get('/chat/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/chat/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::post('/rooms/invitations', [RoomInvitationController::class, 'store'])->name('rooms.invitations.store');
    Route::post('/invitations/{invitation}/accept', [RoomInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/decline', [RoomInvitationController::class, 'decline'])->name('invitations.decline');
});

Route::get('/invitations/{token}', [RoomInvitationController::class, 'show'])->name('invitations.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
