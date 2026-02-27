<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Room Invitation</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-3">
                    <p>You have been invited to join <strong>{{ $invitation->room->name }}</strong>.</p>
                    <p>Invited by {{ $invitation->inviter->name }} ({{ $invitation->inviter->email }}).</p>
                    @if($invitation->expires_at)
                        <p class="text-sm text-gray-500">Expires at {{ $invitation->expires_at->toDayDateTimeString() }}.</p>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Accept Invitation</button>
                        </form>
                    @else
                        <p class="text-sm text-gray-600">Please log in to accept this invitation.</p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>