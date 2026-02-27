<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-900 leading-tight">
            Invitations
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Pending</h3>
                    <div class="space-y-3">
                        @forelse($invitations as $invitation)
                            <div class="border border-slate-200 rounded-lg p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $invitation->room->name }}</p>
                                    <p class="text-xs text-slate-500">Invited by {{ $invitation->inviter->name }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('invitations.accept', $invitation) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('invitations.decline', $invitation) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-md bg-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-300">Decline</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No pending invitations.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>