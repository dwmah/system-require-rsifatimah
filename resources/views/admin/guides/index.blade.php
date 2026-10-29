<x-layouts.admin title="Kelola Panduan Rekrutmen (CMS Panduan)">
    <div class="space-y-6" x-data="{ addModalOpen: false }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Kelola Konten Panduan (CMS)</h1>
                <p class="text-sm text-slate-500 mt-0.5">Pengelolaan dinamis panduan alur pendaftaran dan persyaratan seleksi yang tampil di portal pelamar</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="addModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0179B5] hover:bg-[#016699] text-white font-semibold text-sm rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Bagian Panduan</span>
                </button>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-[#E6F2F8] border border-sky-200 rounded-xl p-4 text-sm text-sky-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-[#0179B5] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <span class="font-bold text-base block mb-0.5">Sinkronisasi Dinamis ke Portal Pelamar</span>
                <p class="text-sky-800 text-xs sm:text-sm leading-relaxed">
                    Setiap pembaruan judul, subjudul, tahapan langkah, atau ketentuan berkas pada halaman ini akan langsung diperbarui dan terlihat oleh publik di menu <strong>Alur Pendaftaran</strong> dan <strong>Persyaratan Pendaftaran</strong>.
                </p>
            </div>
        </div>

        <!-- Grid of Guides Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($guides as $guide)
                @php
                    $isFlow = ($guide->type instanceof \App\Enums\RecruitmentGuideType) ? $guide->type === \App\Enums\RecruitmentGuideType::FLOW : $guide->type === 'flow';
                    $itemCount = is_array($guide->content) ? count($guide->content) : 0;
                    $publicUrl = $isFlow ? route('guides.flow') : route('guides.requirements');
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                    <div class="space-y-4">
                        <!-- Top Meta & Badges -->
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $isFlow ? 'bg-sky-100 text-[#0179B5]' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $isFlow ? 'Alur Pendaftaran' : 'Persyaratan Umum' }}
                            </span>
                            @if($guide->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </div>

                        <!-- Title & Subtitle -->
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $guide->title }}</h2>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                {{ $guide->subtitle ?? 'Tidak ada subjudul keterangan.' }}
                            </p>
                        </div>

                        <!-- Stats & Info -->
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-xs text-slate-600">
                            <div>
                                <span class="text-slate-400">Jumlah Konten:</span>
                                <span class="font-semibold text-slate-800 ml-1">{{ $itemCount }} {{ $isFlow ? 'Tahapan Langkah' : 'Poin Ketentuan' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Urutan:</span>
                                <span class="font-semibold text-slate-800 ml-1">#{{ $guide->sort_order }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-5 mt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.guides.edit', $guide) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0179B5] hover:bg-[#016699] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Edit Konten</span>
                            </a>

                            <form method="POST" action="{{ route('admin.guides.toggle-active', $guide) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-slate-200 text-xs font-medium rounded-lg text-slate-700 hover:bg-slate-50 transition-colors">
                                    {{ $guide->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>

                        <a href="{{ $publicUrl }}" target="_blank"
                           class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-[#0179B5] transition-colors">
                            <span>Lihat Halaman Publik</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
                    <p class="text-sm">Belum ada data panduan rekrutmen.</p>
                </div>
            @endforelse
        </div>

        <!-- Modal Tambah Panduan Baru -->
        <div x-show="addModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs" @click="addModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-base font-bold text-slate-900">Tambah Bagian Panduan Baru</h3>
                    <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.guides.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Panduan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="Contoh: Alur Seleksi Berkas"
                               class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Panduan <span class="text-rose-500">*</span></label>
                        <select name="type" required
                                class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            <option value="flow">Alur Pendaftaran (flow)</option>
                            <option value="requirements">Persyaratan Umum (requirements)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Subjudul / Deskripsi Singkat</label>
                        <textarea name="subtitle" rows="3" placeholder="Deskripsi ringkas mengenai panduan ini..."
                                  class="w-full p-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan Tampil</label>
                            <input type="number" name="sort_order" value="1" min="1"
                                   class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked
                                       class="rounded border-slate-300 text-[#0179B5] focus:ring-[#0179B5]">
                                <span class="text-xs font-medium text-slate-700">Aktifkan Langsung</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200">
                        <button type="button" @click="addModalOpen = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 border border-slate-200 rounded-lg">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#0179B5] hover:bg-[#016699] text-white text-xs font-semibold rounded-lg shadow-2xs">
                            Simpan & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
