@php
    use App\Enums\UserRole;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Campfire-inspired chat by Simao Morais</p>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">Chat</h2>
            </div>
            @if(session('status'))
                <span class="text-sm text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">{{ session('status') }}</span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <script>
                window.__chatProps = {
                    rooms: @json($rooms),
                    directUsers: @json($directUsers),
                    authUser: @json($authUser),
                    invitations: @json($invitations ?? []),
                };
            </script>
            <div class="grid grid-cols-12 gap-6 items-start" x-data="chatPage(window.__chatProps)" x-init="init()">
                <section class="col-span-12 lg:col-span-8">
                    @if($rooms->isEmpty() && $directUsers->isEmpty())
                        <div class="bg-white shadow-sm rounded-xl border border-slate-200 flex items-center justify-center h-[560px]">
                            <p class="text-lg text-slate-500 text-center">No rooms or direct messages available.<br>Please create a room or invite a user.</p>
                        </div>
                        <div class="border-t border-slate-200 px-5 py-4 bg-white rounded-b-xl shadow-sm">
                            <form class="space-y-3">
                                <textarea class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" rows="3" placeholder="Type a message..." disabled></textarea>
                                <div class="flex items-center justify-end text-sm text-slate-500">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-300 text-white rounded-lg font-semibold cursor-not-allowed" disabled>
                                        <span>Send</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-white shadow-sm rounded-xl border border-slate-200">
                            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Active</p>
                                    <h3 class="text-lg font-semibold text-slate-900" x-text="activeLabel"></h3>
                                </div>
                                <div class="text-xs text-slate-500" x-text="pollingLabel"></div>
                            </div>

                            <div class="h-[560px] flex flex-col">
                                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3 bg-slate-50" id="message-list">
                                    <template x-if="messages.length === 0">
                                        <p class="text-sm text-slate-500">No messages yet. Start the conversation.</p>
                                    </template>
                                    <template x-for="message in messages" :key="message.id">
                                        <div class="flex items-start space-x-3">
                                            <template x-if="message.user.avatar">
                                                <img :src="message.user.avatar" alt="" class="h-8 w-8 rounded-full object-cover shadow" />
                                            </template>
                                            <template x-if="!message.user.avatar">
                                                <div class="h-8 w-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-semibold" x-text="message.user.name.substring(0,2).toUpperCase()"></div>
                                            </template>
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-semibold text-slate-900" x-text="message.user.name"></span>
                                                    <span class="text-xs text-slate-500" x-text="formatTime(message.created_at)"></span>
                                                </div>
                                                <p class="text-sm text-slate-800" x-text="message.body"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="border-t border-slate-200 px-5 py-4 bg-white rounded-b-xl">
                                    <form @submit.prevent="sendMessage" class="space-y-3">
                                        <textarea x-model="draft" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" rows="3" placeholder="Type a message..." :disabled="sending || !activeTarget"></textarea>
                                        <div class="flex items-center justify-between text-sm text-slate-500">
                                            <span x-text="statusText"></span>
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50" :disabled="sending || !activeTarget || !draft.trim()">
                                                <span x-text="sending ? 'Sending...' : 'Send'"></span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </section>

                <aside class="col-span-12 lg:col-span-4 space-y-4">
                    <div class="bg-white shadow-sm rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Your Profile</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <label class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500">
                                    @if($authUser->avatar)
                                        <img src="{{ $authUser->avatar }}" alt="Avatar" class="h-10 w-10 object-cover">
                                    @else
                                        <span class="text-xs font-semibold text-slate-700">{{ strtoupper(substr($authUser->name, 0, 2)) }}</span>
                                    @endif
                                    <input type="file" name="avatar" class="hidden" accept="image/*" onchange="this.form.submit()" />
                                </label>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $authUser->name }}</p>
                                    <p class="text-xs text-slate-500">Click avatar to update</p>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">People</h3>
                            <span class="text-xs text-slate-400">{{ $directUsersPaginator->total() }}</span>
                        </div>
                        <div class="space-y-2" id="dm-list">
                            @forelse($directUsers as $dm)
                                <button
                                    type="button"
                                    class="w-full text-left px-3 py-2 rounded-lg border border-transparent transition flex items-center gap-3"
                                    :class="activeTarget?.type === 'user' && activeTarget.id === {{ $dm->id }} ? 'bg-indigo-50 border-indigo-200' : 'hover:bg-slate-50'"
                                    @click="selectTarget('user', {{ $dm->id }}, {{ json_encode($dm->name) }})"
                                >
                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700">{{ strtoupper(substr($dm->name, 0, 2)) }}</div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-800 truncate">{{ $dm->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $dm->email }}</p>
                                    </div>
                                </button>
                            @empty
                                <p class="text-sm text-slate-500">No users available.</p>
                            @endforelse
                        </div>
                        <div class="mt-3">
                            {{ $directUsersPaginator->links() }}
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Rooms</h3>
                            <span class="text-xs text-slate-400">{{ $roomsPaginator->total() }}</span>
                        </div>
                        <div class="space-y-2" id="room-list">
                            @forelse($rooms as $room)
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col w-full gap-2 border border-slate-200 rounded-lg p-2">
                                        <div class="flex items-center gap-3">
                                            <form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data" class="flex items-center">
                                                @csrf
                                                @method('PATCH')
                                                <label class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden cursor-pointer hover:ring-2 hover:ring-indigo-500">
                                                    @if($room->avatar)
                                                        <img src="{{ $room->avatar }}" alt="Room avatar" class="h-10 w-10 object-cover">
                                                    @else
                                                        <span class="text-xs font-semibold text-slate-700">{{ strtoupper(substr($room->name, 0, 2)) }}</span>
                                                    @endif
                                                    <input type="file" name="avatar" class="hidden" accept="image/*" onchange="this.form.submit()" />
                                                </label>
                                            </form>
                                            <div class="flex-1 min-w-0">
                                                <form method="POST" action="{{ route('rooms.update', $room) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="text" name="name" value="{{ $room->name }}" class="w-full text-sm font-medium text-slate-800 border-none focus:ring-0 focus:border-indigo-500" />
                                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-700">Save</button>
                                                </form>
                                                <p class="text-xs text-slate-500">{{ $room->users_count }} members</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    class="text-xs text-slate-700 hover:text-indigo-600"
                                                    @click="selectTarget('room', {{ $room->id }}, {{ json_encode($room->name) }})"
                                                >Open</button>
                                                @if($authUser->role === UserRole::Admin || $authUser->id === $room->created_by)
                                                    <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700">Delete</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('rooms.leave', $room) }}">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-slate-600 hover:text-slate-800">Leave</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No rooms yet.</p>
                            @endforelse
                        </div>
                        <div class="mt-3">
                            {{ $roomsPaginator->links() }}
                        </div>
                    </div>

                    @if($authUser->role === UserRole::Admin)
                        <div class="bg-white shadow-sm rounded-xl p-4 border border-slate-200 space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Create Room</h3>
                                <form class="mt-2 space-y-2" method="POST" action="{{ route('rooms.store') }}">
                                    @csrf
                                    <input type="text" name="name" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Room name" required />
                                    <input type="url" name="avatar" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Avatar URL (optional)" />
                                    <button type="submit" class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-indigo-700 transition">Create</button>
                                </form>
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Send In-App Invitation</h3>
                                @foreach($roomsAll as $room)
                                    @php
                                        $options = $roomInviteOptions[$room->id] ?? collect();
                                    @endphp
                                    <div class="border border-slate-200 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-sm font-semibold text-slate-800">{{ $room->name }}</p>
                                            <span class="text-xs text-slate-500">{{ $room->users_count }} members</span>
                                        </div>
                                        @if($options->isEmpty())
                                            <p class="text-xs text-slate-500">No eligible users to invite.</p>
                                        @else
                                            <form class="space-y-2" method="POST" action="{{ route('rooms.invitations.store') }}">
                                                @csrf
                                                <input type="hidden" name="room_id" value="{{ $room->id }}" />
                                                <select name="invited_user_id" class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                                    <option value="">Select user</option>
                                                    @foreach($options as $candidate)
                                                        <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->email }})</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="w-full bg-slate-900 text-white rounded-lg py-2 text-sm font-semibold hover:bg-slate-800 transition">Invite</button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(($invitations ?? collect())->isNotEmpty())
                        <div class="bg-white shadow-sm rounded-xl p-4 border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Your Invitations</h3>
                                <span class="text-xs text-slate-400">{{ $invitations->count() }}</span>
                            </div>
                            @foreach($invitations as $invitation)
                                <div class="border border-slate-200 rounded-lg p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $invitation->room->name }}</p>
                                            <p class="text-xs text-slate-500">Invited by {{ $invitation->inviter->name }}</p>
                                        </div>
                                        <span class="text-xs text-indigo-600">Pending</span>
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
                            @endforeach
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>

</x-app-layout>