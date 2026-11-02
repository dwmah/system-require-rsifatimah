<x-layouts.public title="Lowongan Pekerjaan" active-menu="lowongan">
    <div class="space-y-6">
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
            <form action="{{ route('vacancies.index') }}" method="GET" id="filter-form">
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
                                class="w-full rounded-lg text-xs py-2 pl-8 pr-8 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden"
                                autocomplete="off"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button
                                type="button"
                                id="clear-search-btn"
                                class="{{ request('q') ? '' : 'hidden' }} absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                title="Hapus kata kunci"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
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
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white cursor-pointer"
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
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white cursor-pointer"
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
                            class="w-full rounded-lg text-xs py-2 px-2.5 border border-slate-300 focus:border-rsi-600 focus:ring-1 focus:ring-rsi-600 focus:outline-hidden bg-white cursor-pointer"
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

            </form>
        </div>

        {{-- Vacancies List Grid Container --}}
        <div id="vacancies-container">
            @if ($vacancies->isEmpty())
                <x-ui.empty-state
                    title="Tidak ada lowongan yang sesuai dengan pencarian atau filter Anda."
                    description="Coba ubah kata kunci atau bersihkan filter unit/pendidikan untuk melihat seluruh formasi yang sedang dibuka."
                >
                    <a
                        href="{{ route('vacancies.index') }}"
                        id="empty-reset-filter-btn"
                        class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors cursor-pointer"
                    >
                        Tampilkan Semua Lowongan
                    </a>
                </x-ui.empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($vacancies as $vacancy)
                        <x-vacancy.card :vacancy="$vacancy" />
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="pt-4" id="pagination-wrapper">
                    {{ $vacancies->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filter-form');
            if (!form) return;

            const searchInput = document.getElementById('search-input');
            const clearSearchBtn = document.getElementById('clear-search-btn');
            const unitSelect = document.getElementById('unit-select');
            const typeSelect = document.getElementById('type-select');
            const eduSelect = document.getElementById('edu-select');
            const vacanciesContainer = document.getElementById('vacancies-container');

            let debounceTimer = null;
            let abortController = null;

            function updateClearButton() {
                if (!clearSearchBtn || !searchInput) return;
                if (searchInput.value.trim().length > 0) {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
            }

            function showLoading() {
                if (vacanciesContainer) {
                    vacanciesContainer.classList.add('opacity-50', 'pointer-events-none', 'transition-opacity', 'duration-200');
                }
            }

            function hideLoading() {
                if (vacanciesContainer) {
                    vacanciesContainer.classList.remove('opacity-50', 'pointer-events-none');
                }
            }

            function performFilter(targetUrl = null) {
                if (abortController) {
                    abortController.abort();
                }
                abortController = new AbortController();

                let fetchUrl = targetUrl;
                if (!fetchUrl) {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        const val = value.trim();
                        if (val !== '') {
                            params.append(key, val);
                        }
                    }
                    const queryString = params.toString();
                    fetchUrl = `${form.action}${queryString ? '?' + queryString : ''}`;
                }

                showLoading();

                fetch(fetchUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: abortController.signal
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newVacancies = doc.getElementById('vacancies-container');
                    if (newVacancies && vacanciesContainer) {
                        vacanciesContainer.innerHTML = newVacancies.innerHTML;
                    }

                    window.history.replaceState({ url: fetchUrl }, '', fetchUrl);
                    updateClearButton();
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Filter fetch fallback to submit:', err);
                        form.submit();
                    }
                })
                .finally(() => {
                    hideLoading();
                });
            }

            // Trigger on select change
            [unitSelect, typeSelect, eduSelect].forEach(select => {
                if (select) {
                    select.addEventListener('change', () => {
                        performFilter();
                    });
                }
            });

            // Trigger on search input with debounce (350ms)
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    updateClearButton();
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        performFilter();
                    }, 350);
                });

                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(debounceTimer);
                        performFilter();
                    }
                });
            }

            // Clear search button
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    updateClearButton();
                    searchInput.focus();
                    performFilter();
                });
            }

            // Prevent default form submit on Enter key
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearTimeout(debounceTimer);
                performFilter();
            });

            // Delegate Reset filter click from empty state
            document.addEventListener('click', (e) => {
                const resetBtn = e.target.closest('#empty-reset-filter-btn');
                if (resetBtn) {
                    e.preventDefault();
                    if (searchInput) searchInput.value = '';
                    if (unitSelect) unitSelect.value = '';
                    if (typeSelect) typeSelect.value = '';
                    if (eduSelect) eduSelect.value = '';
                    updateClearButton();
                    performFilter(form.action);
                }

                // Delegate pagination links inside vacancies container
                const pageLink = e.target.closest('#pagination-wrapper a');
                if (pageLink && pageLink.href) {
                    e.preventDefault();
                    performFilter(pageLink.href);
                    if (vacanciesContainer) {
                        vacanciesContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });

            // Handle browser back/forward navigation
            window.addEventListener('popstate', () => {
                window.location.reload();
            });

            updateClearButton();
        });
    </script>
</x-layouts.public>
