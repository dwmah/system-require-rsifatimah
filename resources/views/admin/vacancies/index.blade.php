<x-layouts.admin title="Manajemen Lowongan SDM">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Lowongan SDM</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola formasi, kualifikasi, kuota penerimaan, dan persyaratan berkas</p>
            </div>
            <a href="{{ route('admin.vacancies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Lowongan Baru</span>
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <form method="GET" action="{{ route('admin.vacancies.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama posisi, kode, unit, atau jenis formasi..."
                           class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white transition-colors">
                </div>

                <div>
                    <select name="status" class="w-full h-10 px-3 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                        <option value="">Semua Status Publikasi</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif / Dipublikasikan</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif / Ditutup</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 h-10 bg-[#0179B5] hover:bg-[#016293] text-white text-xs font-semibold rounded-lg transition-colors">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'formation_type']))
                        <a href="{{ route('admin.vacancies.index') }}" class="h-10 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg flex items-center justify-center transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Vacancies -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Kode & Posisi Formasi</th>
                            <th class="py-3.5 px-4">Unit / Departemen</th>
                            <th class="py-3.5 px-4">Periode</th>
                            <th class="py-3.5 px-4 text-center">Kuota</th>
                            <th class="py-3.5 px-4 text-center">Pelamar</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vacancies as $vac)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $vac->code }}</span>
                                    <div class="font-bold text-slate-900 text-sm mt-0.5">
                                        <a href="{{ route('admin.vacancies.show', $vac) }}" class="hover:text-[#0179B5]">
                                            {{ $vac->title }}
                                        </a>
                                    </div>
                                    <span class="text-[11px] text-slate-500">{{ $vac->formation_type }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700">
                                    {{ $vac->department }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $vac->recruitmentPeriod->name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ $vac->quota }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('admin.applications.index', ['vacancy_id' => $vac->id]) }}" class="inline-flex items-center gap-1 font-bold text-[#0179B5] hover:underline">
                                        <span>{{ $vac->job_applications_count }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($vac->is_published)
                                        <span class="inline-block px-2.5 py-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                                            Aktif / Dibuka
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200 rounded-full">
                                            Ditutup
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.vacancies.show', $vac) }}" title="Lihat Detail & Pelamar" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.vacancies.toggle-publish', $vac) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="{{ $vac->is_published ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}" class="p-1.5 {{ $vac->is_published ? 'bg-amber-50 hover:bg-amber-100 text-amber-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' }} rounded transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.vacancies.edit', $vac) }}" title="Edit Lowongan" class="p-1.5 bg-sky-50 hover:bg-sky-100 text-[#0179B5] rounded transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>

                                        @if($vac->job_applications_count === 0)
                                            <form method="POST" action="{{ route('admin.vacancies.destroy', $vac) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Lowongan" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Tidak ada data lowongan kerja ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vacancies->hasPages())
                <div class="p-4 border-t border-slate-200">
                    {{ $vacancies->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
