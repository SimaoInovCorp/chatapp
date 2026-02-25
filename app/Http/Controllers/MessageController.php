<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Room;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(private readonly MessageService $messageService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'target_type' => ['required', 'in:room,user'],
            'target_id' => ['required', 'integer', 'min:1'],
            'since' => ['nullable', 'date'],
        ]);

        [$targetClass, $target] = $this->resolveTarget($request->string('target_type'), (int) $request->integer('target_id'));

        $this->authorizeTarget($request->user(), $targetClass, $target);

        $query = $target->messages()->with('user')->orderBy('created_at');

        if ($since = $request->input('since')) {
            $query->where('created_at', '>', Carbon::parse($since));
        }

        $messages = $query->limit(200)->get();

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request): MessageResource
    {
        [$targetClass, $target] = $this->resolveTarget($request->string('target_type'), (int) $request->integer('target_id'));

        $this->authorizeTarget($request->user(), $targetClass, $target);

        $message = $this->messageService->send($request->user(), $target, $request->string('body'));

        $message->load('user');

        return new MessageResource($message);
    }

    private function resolveTarget(string $targetType, int $targetId): array
    {
        return match ($targetType) {
            'room' => [Room::class, Room::findOrFail($targetId)],
            'user' => [User::class, User::findOrFail($targetId)],
        };
    }

    private function authorizeTarget(?Authenticatable $user, string $targetClass, object $target): void
    {
        if ($targetClass === Room::class) {
            Gate::authorize('view', $target);
            return;
        }

        if ($targetClass === User::class) {
            return; // DMs allowed between any authenticated users
        }
    }
}