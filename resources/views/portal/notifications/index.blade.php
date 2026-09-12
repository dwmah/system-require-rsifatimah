<x-layouts.applicant title="Notifikasi Rekrutmen">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pemberitahuan & Notifikasi</h1>
                <p class="text-sm text-slate-500 mt-0.5">Riwayat pesan status kelulusan berkas dan instruksi seleksi</p>
            </div>

            @if($notifications->where('read_at', null)->count() > 0)
                <form method="POST" action="{{ route('portal.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <!-- Notification List -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
            @forelse ($notifications as $notif)
                <div class="p-5 flex items-start gap-4 transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/40' : 'hover:bg-slate-50' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $notif->type === 'success' ? 'bg-emerald-100 text-emerald-700' : ($notif->type === 'danger' ? 'bg-rose-100 text-rose-700' : 'bg-sky-100 text-[#0179B5]') }}">
                        @if($notif->type === 'success')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($notif->type === 'danger')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-900 {{ is_null($notif->read_at) ? 'text-[#0179B5]' : '' }}">
                                {{ $notif->title }}
                            </h3>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $notif->message }}
                        </p>

                        <div class="flex items-center gap-3 mt-3">
                            @if($notif->action_url)
                                <a href="{{ $notif->action_url }}" class="text-xs font-semibold text-[#0179B5] hover:underline">
                                    Lihat Detail &rarr;
                                </a>
                            @endif

                            @if(is_null($notif->read_at))
                                <form method="POST" action="{{ route('portal.notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-slate-400 hover:text-slate-600">
                                        Tandai dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <p class="text-sm font-medium">Belum ada notifikasi baru.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.applicant>
