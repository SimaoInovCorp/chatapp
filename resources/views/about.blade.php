<x-guest-layout>
    <div class="space-y-6">
        <div class="bg-white shadow-sm rounded-xl p-6 border border-slate-200">
            <h1 class="text-2xl font-semibold text-slate-900 mb-2">About ChatApp</h1>
            <p class="text-slate-600">A modern chat platform built to be secure, collaborative, and fast.</p>
        </div>

        <div class="grid gap-4">
            <div class="bg-sky-50 border border-sky-100 rounded-xl p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-2">Tech Stack</h2>
                <ul class="list-disc list-inside text-slate-700 space-y-1">
                    <li>Laravel (routing, policies, queues)</li>
                    <li>Alpine.js (interactive UI)</li>
                    <li>Tailwind CSS (styling)</li>
                    <li>Vite (bundling)</li>
                    <li>SQLite / SQL DB (persistence)</li>
                </ul>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-2">Roadmap</h2>
                <ul class="list-disc list-inside text-slate-700 space-y-1">
                    <li>Real-time chat (WebSockets)</li>
                    <li>File sharing & attachments</li>
                    <li>Typing indicators & read receipts</li>
                    <li>Room moderation tools & audit logs</li>
                </ul>
            </div>
        </div>
    </div>
</x-guest-layout>