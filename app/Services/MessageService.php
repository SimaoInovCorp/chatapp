<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class MessageService
{
    public function send(Authenticatable $user, Model $target, string $body): Message
    {
        return $target->messages()->create([
            'user_id' => $user->getAuthIdentifier(),
            'body' => $body,
        ]);
    }
}