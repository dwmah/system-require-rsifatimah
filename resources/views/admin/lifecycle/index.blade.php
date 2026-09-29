<x-layouts.admin title="Siklus Akun & Arsip Pasca-Seleksi">
    <div class="space-y-6" x-data="{ tab: '{{ request('tab', 'lolos') }}' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengelolaan Siklus Akun Pengguna (User Lifecycle)</h1>
                <p class="text-sm text-slate-500 mt-1">Manajemen arsip data pelamar lolos dan pembersihan data pelamar tidak lolos sesuai SOP</p>
            </div>
        </div>

        <!-- Policy Explanatory Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Aturan Pelamar Lolos -->
            <div class="bg-emerald-50/80 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-950 space-y-1.5">
                <div class="font-bold flex items-center gap-2 text-base text-emerald-900">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>Kebijakan Pelamar Lolos (Arsip Kepegawaian)</span>
                </div>
                <p class="text-emerald-800 leading-relaxed text-sm">
                    Akun dinonaktifkan (deactivated) setelah proses seleksi berakhir sehingga tidak dapat mengakses portal pendaftaran kembali, namun <strong>seluruh berkas, riwayat kualifikasi, dan biodata pelamar tetap tersimpan utuh</strong> sebagai arsip kepegawaian baru.
                </p>
            </div>

            <!-- Aturan Pelamar Tidak Lolos -->
            <div class="bg-rose-50/80 border border-rose-200 rounded-xl p-4 text-sm text-rose-950 space-y-1.5">
                <div class="font-bold flex items-center gap-2 text-base text-rose-900">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                    <span>Kebijakan Pelamar Tidak Lolos (Pembersihan Data)</span>
                </div>
                <p class="text-rose-800 leading-relaxed text-sm">
                    Data dan berkas fisik pelamar yang tidak lolos dapat dibersihkan secara aman (hard delete) setelah pelamar membaca dan mengonfirmasi pengumuman, guna melindungi privasi kandidat dan menghemat kapasitas server.
                </p>
            </div>
        </div>

        <!-- Filter Form (Auto-Filter on Change) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('admin.lifecycle.index') }}" class="flex flex-col sm:flex-row gap-3 items-center">
                <input type="hidden" name="tab" :value="tab">

                <div class="flex-1 w-full relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, email, No. Reg... (Tekan Enter)"
                           class="w-full h-11 pl-10 pr-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white transition-colors">
                </div>

                <div class="w-full sm:w-60">
                    <select name="vacancy_id" onchange="this.form.submit()" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] cursor-pointer">
                        <option value="">Semua Posisi Formasi</option>
                        @foreach ($vacancies as $vac)
                            <option value="{{ $vac->id }}" {{ request('vacancy_id') == $vac->id ? 'selected' : '' }}>
                                {{ $vac->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-52">
                    <select name="status" onchange="this.form.submit()" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] cursor-pointer">
                        <option value="">Semua Status Seleksi</option>
                        <option value="passed_administration" {{ request('status') === 'passed_administration' ? 'selected' : '' }}>Lolos Administrasi</option>
                        <option value="in_selection" {{ request('status') === 'in_selection' ? 'selected' : '' }}>Tahap Seleksi</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Tidak Lolos</option>
                    </select>
                </div>

                <div class="w-full sm:w-52">
                    <select name="account_status" onchange="this.form.submit()" class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] cursor-pointer">
                        <option value="">Semua Status Akun</option>
                        <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Akun Aktif</option>
                        <option value="inactive" {{ request('account_status') === 'inactive' ? 'selected' : '' }}>Dinonaktifkan (Arsip)</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'vacancy_id', 'status', 'account_status']))
                    <a href="{{ route('admin.lifecycle.index', ['tab' => request('tab', 'lolos')]) }}" title="Reset Semua Filter" class="h-11 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg flex items-center justify-center gap-1.5 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Reset</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tab Toggle -->
        <div class="flex items-center gap-4 border-b border-slate-200 text-sm font-semibold">
            <button @click="tab = 'lolos'" :class="tab === 'lolos' ? 'border-[#0179B5] text-[#0179B5] border-b-2 font-bold' : 'text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition-colors flex items-center gap-2 cursor-pointer">
                <span>1. Arsip Pelamar Lolos & Calon Karyawan</span>
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">{{ $passedApplications->total() }}</span>
            </button>

            <button @click="tab = 'tidak_lolos'" :class="tab === 'tidak_lolos' ? 'border-[#0179B5] text-[#0179B5] border-b-2 font-bold' : 'text-slate-500 hover:text-slate-800'" class="pb-3 px-1 transition-colors flex items-center gap-2 cursor-pointer">
                <span>2. Pembersihan Data Pelamar Tidak Lolos</span>
                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded-full text-xs font-bold">{{ $rejectedApplications->total() }}</span>
            </button>
        </div>

        <!-- TAB 1: ARSIP PELAMAR LOLOS -->
        <div x-show="tab === 'lolos'" class="space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-slate-900">Daftar Pelamar Lolos Administrasi &amp; Diterima</span>
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md text-xs font-bold">{{ $passedApplications->total() }} Kandidat</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Arsip data kepegawaian calon karyawan baru yang telah lolos seleksi.</p>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        @if($acceptedCount > 0)
                            <a href="{{ route('admin.lifecycle.export-accepted-biodata', request()->only(['vacancy_id'])) }}" 
                               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow-md transition-all flex items-center gap-2 cursor-pointer group"
                               title="Unduh seluruh biodata pelamar yang diterima dalam format Excel (.xlsx)">
                                <svg class="w-4 h-4 text-emerald-100 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Unduh Biodata Diterima (.xlsx)</span>
                            </a>
                        @else
                            <button type="button" disabled class="px-3.5 py-2 bg-slate-100 text-slate-400 border border-slate-200 text-xs font-semibold rounded-xl flex items-center gap-2 cursor-not-allowed" title="Belum ada pelamar dengan status diterima">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Unduh Biodata (.xlsx)</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-xs">
                            <tr>
                                <th class="py-3 px-4">Nama Pelamar</th>
                                <th class="py-3 px-4">Formasi</th>
                                <th class="py-3 px-4">No. Registrasi</th>
                                <th class="py-3 px-4">Status Seleksi</th>
                                <th class="py-3 px-4 text-center">Status Akun</th>
                                <th class="py-3 px-4 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($passedApplications as $app)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $app->user->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $app->user->email }} &bull; {{ $app->user->phone ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-700">
                                        {{ $app->jobVacancy->title }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-semibold text-slate-600">
                                        {{ $app->user->registration_number ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full border {{ $app->status->badgeClass() }}">
                                            {{ $app->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($app->user->is_active)
                                            <span class="inline-block px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 text-xs font-bold bg-slate-200 text-slate-700 rounded-full">
                                                Dinonaktifkan (Arsip)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.applications.show', $app) }}" class="px-3 py-1.5 bg-sky-50 text-[#0179B5] hover:bg-sky-100 font-semibold rounded-lg text-xs transition-colors">
                                                Lihat Berkas
                                            </a>

                                            @if($app->user->is_active)
                                                <form method="POST" action="{{ route('admin.lifecycle.deactivate', $app->user) }}" onsubmit="return confirm('Nonaktifkan akun pelamar ini? (Data dan berkas tetap tersimpan utuh di arsip)')">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded-lg text-xs transition-colors">
                                                        Nonaktifkan Akun
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.lifecycle.reactivate', $app->user) }}">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition-colors">
                                                        Aktifkan Lagi
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        Belum ada pelamar yang dinyatakan lolos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($passedApplications->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $passedApplications->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: PEMBERSIHAN DATA PELAMAR TIDAK LOLOS -->
        <div x-show="tab === 'tidak_lolos'" class="space-y-4">
            <!-- Bulk Action Header -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="font-bold text-sm text-slate-800">Antrean Pembersihan Data Pelamar Tidak Lolos</span>
                    <p class="text-xs text-slate-500 mt-1">
                        Terdapat <strong>{{ $seenCount }}</strong> pelamar yang telah membuka & mengonfirmasi pengumuman penolakannya.
                    </p>
                </div>

                @if($seenCount > 0)
                    <form method="POST" action="{{ route('admin.lifecycle.cleanup-all-seen') }}" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin membersihkan data & file fisik seluruh pelamar tidak lolos yang telah membuka pengumuman?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Bersihkan {{ $seenCount }} Data Terkonfirmasi</span>
                        </button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 text-xs">
                            <tr>
                                <th class="py-3 px-4">Nama Pelamar</th>
                                <th class="py-3 px-4">Formasi</th>
                                <th class="py-3 px-4">Alasan Penolakan</th>
                                <th class="py-3 px-4 text-center">Status Pengumuman</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($rejectedApplications as $app)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $app->user->name ?? 'User Telah Dihapus' }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $app->user->email ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-slate-700">
                                        {{ $app->jobVacancy->title }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 max-w-[250px] truncate" title="{{ $app->rejection_reason }}">
                                        {{ $app->rejection_reason ?? 'Berkas tidak lengkap' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($app->announcement_seen_at)
                                            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                                ✓ Dibuka ({{ $app->announcement_seen_at->format('d/m/y H:i') }})
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-600 rounded-full">
                                                Belum Dibuka Pelamar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        @if($app->user)
                                            <form method="POST" action="{{ route('admin.lifecycle.cleanup', $app->user) }}" onsubmit="return confirm('Hapus permanen akun & seluruh berkas fisik {{ $app->user->name }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold rounded-lg text-xs transition-colors">
                                                    Hapus Permanen
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        Tidak ada pelamar dalam antrean penolakan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rejectedApplications->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $rejectedApplications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
