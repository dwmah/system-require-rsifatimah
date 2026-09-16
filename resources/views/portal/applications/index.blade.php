<x-layouts.applicant title="Lamaran Saya — Rekrutmen RSI Fatimah">
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="pb-4 border-b border-slate-200">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                Lamaran Saya
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Daftar riwayat dan status lamaran pekerjaan yang telah Anda ajukan di RSI Fatimah Banyuwangi.
            </p>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('info'))
            <x-ui.alert type="info">
                {{ session('info') }}
            </x-ui.alert>
        @endif

        @if (session('warning'))
            <x-ui.alert type="warning">
                {{ session('warning') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Applications List --}}
        @if ($applications->isEmpty())
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200 shadow-2xs space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">
                    Belum Ada Lamaran Diajukan
                </h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    Anda belum memiliki draft atau riwayat berkas lamaran pada sistem seleksi RSI Fatimah. Silakan cek lowongan yang sedang dibuka.
                </p>
                <div class="pt-2">
                    <a
                        href="{{ route('vacancies.index') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-rsi-700 hover:bg-rsi-800 transition-colors shadow-2xs"
                    >
                        <span>Lihat Lowongan Dibuka</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($applications as $app)
                    @php
                        $isDraft = $app->status->value === 'draft';
                        $safeStatus = $app->safe_status ?? [
                            'label' => $app->status->label(),
                            'badge_class' => 'bg-slate-100 text-slate-700 border-slate-200',
                        ];
                    @endphp

                    <div
                        x-data="{ showCancelModal: false }"
                        class="bg-white rounded-2xl border border-slate-200 hover:border-slate-300 transition-colors p-5 sm:p-6 shadow-2xs space-y-4"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-rsi-800 bg-rsi-50 px-2.5 py-0.5 rounded border border-rsi-100">
                                        {{ $app->jobVacancy->code }}
                                    </span>
                                    <span class="text-xs text-slate-400">&bull;</span>
                                    <span class="text-xs font-medium text-slate-500">
                                        {{ $app->recruitmentPeriod->name }}
                                    </span>
                                </div>

                                <h2 class="text-base sm:text-lg font-bold text-slate-900">
                                    {{ $app->jobVacancy->title }}
                                </h2>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                    <div>
                                        <span>Nomor Lamaran: </span>
                                        <strong class="text-slate-700 font-mono">
                                            {{ $app->application_number ?? 'Belum diterbitkan' }}
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Dibuat: </span>
                                        <strong class="text-slate-700">
                                            {{ $app->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 self-start sm:self-auto">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $safeStatus['badge_class'] }}">
                                    {{ $safeStatus['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Bottom: Action CTAs --}}
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            @if ($isDraft)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a
                                        href="{{ route('vacancies.index') }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-rsi-200 text-rsi-700 hover:bg-rsi-50 transition-colors shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <span>Ganti Formasi</span>
                                    </a>

                                    <button
                                        type="button"
                                        @click="showCancelModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition-colors shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Batalkan Draft</span>
                                    </button>
                                </div>

                                <div class="flex items-center sm:justify-end">
                                    <a
                                        href="{{ route('portal.applications.show', $app) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold bg-rsi-700 text-white hover:bg-rsi-800 transition-colors shadow-2xs"
                                    >
                                        <span>Lanjutkan Lamaran</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            @else
                                <div></div>
                                <div class="flex items-center gap-3">
                                    <a
                                        href="{{ route('portal.applications.tracking', $app) }}"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-rsi-700 hover:text-rsi-800 hover:underline"
                                    >
                                        <span>Status Seleksi</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                    <span class="text-slate-300">&bull;</span>
                                    <a
                                        href="{{ route('portal.applications.show', $app) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:underline"
                                    >
                                        <span>Detail Lamaran</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Modal: Batalkan Draft Lamaran --}}
                        @if ($isDraft)
                            <template x-teleport="body">
                                <div
                                    x-show="showCancelModal"
                                    x-cloak
                                    class="fixed inset-0 z-50 overflow-y-auto"
                                    role="dialog"
                                    aria-modal="true"
                                >
                                    {{-- Backdrop --}}
                                    <div
                                        x-show="showCancelModal"
                                        x-transition:enter="ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
                                        @click="showCancelModal = false"
                                        aria-hidden="true"
                                    ></div>

                                    {{-- Dialog Wrapper --}}
                                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
                                        <div
                                            x-show="showCancelModal"
                                            x-transition:enter="ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave="ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                            class="relative w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto space-y-4"
                                        >
                                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-bold text-slate-900">Batalkan Draft & Hapus Akun?</h3>
                                                        <p class="text-xs text-slate-500">Formasi: {{ $app->jobVacancy->title }}</p>
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="showCancelModal = false"
                                                    class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                                >
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="space-y-2.5 text-xs">
                                                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-900 leading-relaxed">
                                                    <strong class="block font-bold text-rose-950 flex items-center gap-1.5 mb-0.5">
                                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        Peringatan Pembatalan Akun & Lamaran:
                                                    </strong>
                                                    Sesuai kebijakan RSI Fatimah, akun pelamar wajib memiliki formasi lamaran aktif. Membatalkan draft lamaran ini akan <strong>menghapus akun dan seluruh data pendaftaran Anda</strong> dari sistem, serta sesi login Anda akan diakhiri.
                                                </div>

                                                <div class="p-3 bg-sky-50 rounded-xl border border-sky-200 text-sky-900 leading-relaxed">
                                                    <strong class="block font-bold text-sky-950 flex items-center gap-1.5 mb-0.5">
                                                        <svg class="w-4 h-4 text-sky-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                        </svg>
                                                        Hanya Ingin Mengganti Formasi?
                                                    </strong>
                                                    Jika Anda hanya ingin melamar posisi lowongan lain, <strong>jangan batalkan draft</strong>. Klik tombol Kembali dan gunakan tombol <strong>Ganti Formasi</strong> agar data profil Anda tetap tersimpan.
                                                </div>

                                                <p class="text-slate-500 text-[11px] leading-relaxed">
                                                    Sebanyak <strong>{{ $app->documents->count() }} berkas</strong> dokumen yang telah diunggah untuk formasi ini juga akan dihapus secara permanen dari server.
                                                </p>
                                            </div>

                                            <form method="POST" action="{{ route('portal.applications.cancel-draft', $app) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                                                    <button
                                                        type="button"
                                                        @click="showCancelModal = false"
                                                        class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                                    >
                                                        Kembali
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-xs transition-colors cursor-pointer"
                                                    >
                                                        Ya, Batalkan Draft & Hapus Akun
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.applicant>
