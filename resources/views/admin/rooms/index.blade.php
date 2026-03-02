@php
    use App\Enums\UserRole;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Room management & invitations</p>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">Manage Rooms</h2>
            </div>
            <a href="{{ route('chat') }}" class="text-sm text-indigo-700 hover:text-indigo-800">Back to chat</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <div class="bg-emerald-50 text-emerald-800 px-4 py-2 rounded-lg border border-emerald-200 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-12 gap-6">
                <section class="col-span-12 lg:col-span-5 space-y-4">
                    <div class="bg-white shadow-sm rounded-xl p-5 border border-slate-200">
                        <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wide mb-3">Create a new room</h3>
                        <form class="space-y-3" method="POST" action="{{ route('rooms.store') }}">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                <input type="text" name="name" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Room name" required />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Avatar URL (optional)</label>
                                <input type="url" name="avatar" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://..." />
                                <p class="text-xs text-slate-500 mt-1">You can upload an image after creating the room.</p>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-indigo-700 transition">Create room</button>
                        </form>
                    </div>
                </section>

                <section class="col-span-12 lg:col-span-7 space-y-4">
                    @foreach($rooms as $room)
                        @php
                            $options = $roomInviteOptions[$room->id] ?? collect();
                        @endphp
                        <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-4 space-y-4">
                            <div class="flex items-start gap-3">
                                <form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data" class="flex items-center">
                                    @csrf
                                    @method('PATCH')
                                    <label class="h-12 w-12 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500">
                                        @if($room->avatar)
                                            <img src="{{ $room->avatar }}" alt="Room avatar" class="h-12 w-12 object-cover">
                                        @else
                                            <span class="text-sm font-semibold text-slate-700">{{ strtoupper(substr($room->name, 0, 2)) }}</span>
                                        @endif
                                        <input type="file" name="avatar" class="hidden" accept="image/*" onchange="this.form.submit()" />
                                    </label>
                                </form>
                                <div class="flex-1 min-w-0 space-y-2">
                                    <form method="POST" action="{{ route('rooms.update', $room) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="name" value="{{ $room->name }}" class="w-full text-base font-semibold text-slate-900 border-none focus:ring-0 focus:border-indigo-500" />
                                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-700">Save</button>
                                    </form>
                                    <p class="text-xs text-slate-500">{{ $room->users_count }} members • Created {{ $room->created_at?->diffForHumans() }}</p>
                                    <div class="flex items-center gap-2 text-xs">
                                        <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-semibold text-slate-800">Send in-app invitation</h4>
                                    <span class="text-xs text-slate-500">{{ $options->count() }} available</span>
                                </div>
                                @if($options->isEmpty())
                                    <p class="text-xs text-slate-500">No eligible users to invite.</p>
                                @else
                                    <form class="flex flex-col gap-2 sm:flex-row sm:items-center" method="POST" action="{{ route('rooms.invitations.store') }}">
                                        @csrf
                                        <input type="hidden" name="room_id" value="{{ $room->id }}" />
                                        <select name="invited_user_id" class="flex-1 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                            <option value="">Select user</option>
                                            @foreach($options as $candidate)
                                                <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->email }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">Invite</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div>
                        {{ $rooms->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
