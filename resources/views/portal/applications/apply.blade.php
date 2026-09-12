<x-layouts.applicant title="Pilih Formasi Lowongan">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('portal.home') }}" class="hover:text-[#0179B5]">Portal Pelamar</a>
            <span>/</span>
            <a href="{{ route('portal.applications.index') }}" class="hover:text-[#0179B5]">Lamaran</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Pilih Formasi</span>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-6">
            <div class="pb-4 border-b border-slate-200">
                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full uppercase tracking-wider inline-block mb-1">
                    Periode Aktif: {{ $activePeriod->name }}
                </span>
                <h1 class="text-xl font-bold text-slate-900">Pilih Posisi Formasi Lowongan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Satu akun pelamar hanya dapat mengajukan 1 (satu) formasi dalam satu periode rekrutmen</p>
            </div>

            <form method="POST" action="{{ route('portal.applications.store') }}" class="space-y-6">
                @csrf

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Daftar Formasi Tersedia:
                    </label>

                    <div class="space-y-3">
                        @forelse ($vacancies as $vac)
                            <label class="p-4 bg-slate-50 hover:bg-sky-50/50 border border-slate-200 hover:border-[#0179B5] rounded-xl flex items-start gap-4 cursor-pointer transition-colors block">
                                <input type="radio" name="job_vacancy_id" value="{{ $vac->id }}" {{ $selectedVacancyId == $vac->id ? 'checked' : '' }} required
                                       class="mt-1 w-4 h-4 text-[#0179B5] border-slate-300 focus:ring-[#0179B5]">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $vac->title }}</h3>
                                        <span class="text-[11px] font-bold text-[#0179B5] bg-white px-2.5 py-0.5 rounded-full border border-sky-100">
                                            Kuota: {{ $vac->quota }} Orang
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $vac->department }} &bull; {{ $vac->formation_type }}</div>
                                    @if($vac->summary)
                                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $vac->summary }}</p>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 py-6 text-center">Belum ada formasi lowongan yang dibuka saat ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ route('portal.applications.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        Konfirmasi & Pilih Formasi Ini &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.applicant>
