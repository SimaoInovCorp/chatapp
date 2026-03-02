@php
    use App\Enums\UserRole;
    use App\Enums\UserStatus;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500">Admin only</p>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">User Management</h2>
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
            @if($errors->any())
                <div class="bg-red-50 text-red-800 px-4 py-2 rounded-lg border border-red-200 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Joined</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover">
                                        @else
                                            <div class="h-9 w-9 rounded-full bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $user->role->value }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $user->status?->value ?? '' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $user->created_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700" @if(auth()->id() === $user->id) disabled @endif>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
