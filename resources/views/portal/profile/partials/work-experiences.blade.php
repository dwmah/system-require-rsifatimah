@props([
    'workExperiences' => collect(),
])

<div
    class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7"
    x-data="{
        showModal: false,
        isEdit: false,
        modalTitle: 'Tambah Pengalaman Kerja',
        formAction: '{{ route('profile.work-experiences.store') }}',
        formData: {
            id: null,
            company_name: '',
            position: '',
            start_date: '',
            end_date: '',
            description: ''
        },
        openCreate() {
            this.isEdit = false;
            this.modalTitle = 'Tambah Pengalaman Kerja';
            this.formAction = '{{ route('profile.work-experiences.store') }}';
            this.formData = {
                id: null,
                company_name: '',
                position: '',
                start_date: '',
                end_date: '',
                description: ''
            };
            this.showModal = true;
        },
        openEdit(item) {
            this.isEdit = true;
            this.modalTitle = 'Edit Pengalaman Kerja';
            this.formAction = '/portal/profil/pengalaman/' + item.id;
            this.formData = {
                id: item.id,
                company_name: item.company_name,
                position: item.position,
                start_date: item.start_date ? item.start_date.substring(0, 10) : '',
                end_date: item.end_date ? item.end_date.substring(0, 10) : '',
                description: item.description || ''
            };
            this.showModal = true;
        }
    }"
>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-slate-100 gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Riwayat Pengalaman Kerja</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    OPSIONAL / FRESH GRADUATE
                </span>
            </div>
            <p class="text-xs text-slate-500">Pengalaman kerja relevan sebelumnya (boleh dikosongkan bagi pelamar lulusan baru).</p>
        </div>

        <div>
            <button
                type="button"
                @click="openCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rsi-700 bg-rsi-50 hover:bg-rsi-100 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pengalaman</span>
            </button>
        </div>
    </div>

    {{-- Cards List --}}
    @if ($workExperiences->isEmpty())
        <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400">
            <p class="text-xs font-medium text-slate-600">Belum ada pengalaman kerja yang ditambahkan</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Bagi pelamar yang belum memiliki pengalaman kerja, bagian ini dapat dilewati.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($workExperiences as $work)
                <div class="p-4 bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-slate-900">{{ $work->position }}</h4>
                            <span class="text-xs text-slate-400">&bull;</span>
                            <span class="text-xs font-semibold text-slate-700">{{ $work->company_name }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500">
                            <span>
                                Periode: <strong>{{ $work->start_date ? $work->start_date->format('M Y') : '-' }}</strong> s/d
                                <strong>{{ $work->end_date ? $work->end_date->format('M Y') : 'Sekarang (Aktif)' }}</strong>
                            </span>
                        </div>
                        @if ($work->description)
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $work->description }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button
                            type="button"
                            @click="openEdit({{ json_encode($work) }})"
                            class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-rsi-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                        >
                            Edit
                        </button>
                        <form method="POST" action="{{ route('profile.work-experiences.destroy', $work) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengalaman kerja ini?')">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="px-2.5 py-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal Tambah/Edit Pengalaman Kerja --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-2xs transition-opacity"
                @click="showModal = false"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-slate-200"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 focus:outline-hidden">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PATCH">
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Instansi / Perusahaan <span class="text-red-500 font-bold">*</span>
                        </label>
                        <input
                            type="text"
                            name="company_name"
                            x-model="formData.company_name"
                            placeholder="Contoh: RS Islam Fatimah Banyuwangi / PT ..."
                            required
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Posisi / Jabatan <span class="text-red-500 font-bold">*</span>
                        </label>
                        <input
                            type="text"
                            name="position"
                            x-model="formData.position"
                            placeholder="Contoh: Perawat Pelaksana / Staf IT"
                            required
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tanggal Mulai
                            </label>
                            <input
                                type="date"
                                name="start_date"
                                x-model="formData.start_date"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tanggal Selesai
                            </label>
                            <input
                                type="date"
                                name="end_date"
                                x-model="formData.end_date"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                            <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika masih bekerja saat ini.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Deskripsi Pekerjaan & Tanggung Jawab
                        </label>
                        <textarea
                            name="description"
                            x-model="formData.description"
                            rows="3"
                            placeholder="Jelaskan secara singkat tugas dan pencapaian selama bekerja..."
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                        ></textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <x-ui.button type="submit" variant="primary" size="sm">
                            Simpan Pengalaman
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
