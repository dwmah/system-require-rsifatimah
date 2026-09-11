<x-layouts.public title="Lowongan Pekerjaan" active-menu="lowongan">
    <div class="space-y-6">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Lowongan Pekerjaan']
        ]" />

        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Lowongan Pekerjaan
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Pilih formasi tenaga medis, penunjang medis, atau non-medis yang dibuka pada periode rekrutmen aktif.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.badge variant="success" dot size="lg">
                    {{ $totalOpenVacancies }} Formasi Dibuka
                </x-ui.badge>
            </div>
        </div>

        {{-- Search & Filter Section --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <form action="{{ route('vacancies.index') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    {{-- Search Keyword Input --}}
                    <div>
                        <label for="search-input" class="block text-xs font-semibold text-slate-700 mb-1">
                            Pencarian Kata Kunci
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                name="q"
                                id="search-input"
                                value="{{ request('q') }}"
                                placeholder="Posisi, unit, atau kode..."
                                class="w-full rounded-lg text-xs py-2 pl-8 pr-3 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Unit Filter --}}
                    <div>
                        <label for="unit-select" class="block text-xs font-semibold text-slate-700 mb-1">
                            Unit Kerja / Instalasi
                        </label>
                        <select
                            name="unit"
                            id="unit-select"
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white"
                        >
                            <option value="">Semua Unit</option>
                            @foreach ($availableUnits as $unit)
                                <option value="{{ $unit }}" {{ request('unit') === $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Employment Type Filter --}}
                    <div>
                        <label for="type-select" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenis Kepegawaian
                        </label>
                        <select
                            name="employment_type"
                            id="type-select"
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white"
                        >
                            <option value="">Semua Jenis</option>
                            @foreach ($availableEmploymentTypes as $type)
                                <option value="{{ $type }}" {{ request('employment_type') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Education Filter --}}
                    <div>
                        <label for="edu-select" class="block text-xs font-semibold text-slate-700 mb-1">
                            Jenjang Pendidikan
                        </label>
                        <select
                            name="education"
                            id="edu-select"
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white"
                        >
                            <option value="">Semua Jenjang</option>
                            @foreach ($educationTiers as $key => $label)
                                <option value="{{ $key }}" {{ request('education') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <div>
                        @if (request()->hasAny(['q', 'unit', 'employment_type', 'education']))
                            <a
                                href="{{ route('vacancies.index') }}"
                                class="text-xs text-red-600 hover:text-red-800 font-medium inline-flex items-center gap-1 focus:outline-hidden focus:underline"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Reset Filter</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.button type="submit" variant="primary" size="sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan Filter</span>
                        </x-ui.button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Vacancies List Grid --}}
        @if ($vacancies->isEmpty())
            <x-ui.empty-state
                title="Tidak ada lowongan yang sesuai dengan pencarian atau filter Anda."
                description="Coba ubah kata kunci atau bersihkan filter unit/pendidikan untuk melihat seluruh formasi yang sedang dibuka."
            >
                <x-ui.button :href="route('vacancies.index')" variant="secondary" size="sm">
                    Tampilkan Semua Lowongan
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($vacancies as $vacancy)
                    <x-vacancy.card :vacancy="$vacancy" />
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="pt-4">
                {{ $vacancies->links() }}
            </div>
        @endif
    </div>
</x-layouts.public>
