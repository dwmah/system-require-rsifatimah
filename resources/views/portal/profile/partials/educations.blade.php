@props([
    'educations' => collect(),
])

<div
    class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7"
    x-data="{
        showModal: false,
        isEdit: false,
        modalTitle: 'Tambah Riwayat Pendidikan',
        formAction: '{{ route('profile.educations.store') }}',
        formData: {
            id: null,
            education_level: '',
            institution_name: '',
            study_program: '',
            accreditation: '',
            gpa: '',
            graduation_year: ''
        },
        openCreate() {
            this.isEdit = false;
            this.modalTitle = 'Tambah Riwayat Pendidikan';
            this.formAction = '{{ route('profile.educations.store') }}';
            this.formData = {
                id: null,
                education_level: 'D3',
                institution_name: '',
                study_program: '',
                accreditation: '',
                gpa: '',
                graduation_year: '{{ now()->year }}'
            };
            this.showModal = true;
        },
        openEdit(item) {
            this.isEdit = true;
            this.modalTitle = 'Edit Riwayat Pendidikan';
            this.formAction = '/portal/profil/pendidikan/' + item.id;
            this.formData = {
                id: item.id,
                education_level: item.education_level,
                institution_name: item.institution_name,
                study_program: item.study_program,
                accreditation: item.accreditation || '',
                gpa: item.gpa || '',
                graduation_year: item.graduation_year || ''
            };
            this.showModal = true;
        }
    }"
>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-slate-100 gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Riwayat Pendidikan</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    WAJIB (MIN. 1 FORMAL)
                </span>
            </div>
            <p class="text-xs text-slate-500">Daftar pendidikan formal terakhir yang telah diselesaikan.</p>
        </div>

        <div>
            <button
                type="button"
                @click="openCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-rsi-700 hover:bg-rsi-800 transition-colors shadow-2xs cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pendidikan</span>
            </button>
        </div>
    </div>

    {{-- Cards List --}}
    @if ($educations->isEmpty())
        <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-500">
            <svg class="w-8 h-8 mx-auto text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
            <p class="text-xs font-semibold text-slate-700">Belum ada riwayat pendidikan yang ditambahkan</p>
            <p class="text-[11px] text-slate-400 mt-1">Anda wajib menambahkan minimal 1 riwayat pendidikan formal agar profil siap melamar.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($educations as $edu)
                <div class="p-4 bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rsi-50 text-rsi-800">
                                {{ $edu->education_level }}
                            </span>
                            <h4 class="text-sm font-bold text-slate-900">{{ $edu->institution_name }}</h4>
                        </div>
                        <p class="text-xs text-slate-600 font-medium">Program Studi: {{ $edu->study_program }}</p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 pt-0.5">
                            @if ($edu->graduation_year)
                                <span>Lulus Tahun: <strong>{{ $edu->graduation_year }}</strong></span>
                            @endif
                            @if ($edu->gpa)
                                <span>IPK: <strong>{{ number_format((float) $edu->gpa, 2) }}</strong></span>
                            @endif
                            @if ($edu->accreditation)
                                <span>Akreditasi: <strong>{{ $edu->accreditation }}</strong></span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button
                            type="button"
                            @click="openEdit({{ json_encode($edu) }})"
                            class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-rsi-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                        >
                            Edit
                        </button>
                        <form method="POST" action="{{ route('profile.educations.destroy', $edu) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pendidikan ini?')">
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

    {{-- Modal Form Tambah/Edit --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-education-title"
        role="dialog"
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div
            x-show="showModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-2xs transition-opacity"
            @click="showModal = false"
            aria-hidden="true"
        ></div>

        {{-- Dialog Wrapper --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto"
            >
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-base font-bold text-slate-900" id="modal-education-title" x-text="modalTitle"></h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer">
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

                    {{-- Jenjang & Institusi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Jenjang Pendidikan <span class="text-red-500 font-bold">*</span>
                            </label>
                            <select
                                name="education_level"
                                x-model="formData.education_level"
                                required
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 bg-white text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                                <option value="SMA/SMK">SMA / SMK / Sederajat</option>
                                <option value="D3">D3 (Diploma 3)</option>
                                <option value="D4">D4 (Diploma 4)</option>
                                <option value="S1">S1 (Sarjana)</option>
                                <option value="Profesi">Profesi / Ners / Apoteker</option>
                                <option value="S2">S2 (Magister)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tahun Kelulusan
                            </label>
                            <input
                                type="number"
                                name="graduation_year"
                                x-model="formData.graduation_year"
                                min="1970"
                                max="{{ now()->year }}"
                                placeholder="Contoh: {{ now()->year }}"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Institusi / Universitas <span class="text-red-500 font-bold">*</span>
                            </label>
                            <input
                                type="text"
                                name="institution_name"
                                x-model="formData.institution_name"
                                placeholder="Contoh: Universitas Airlangga"
                                required
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Program Studi / Jurusan <span class="text-red-500 font-bold">*</span>
                            </label>
                            <input
                                type="text"
                                name="study_program"
                                x-model="formData.study_program"
                                placeholder="Contoh: D3 Keperawatan"
                                required
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                IPK / Nilai
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="4"
                                name="gpa"
                                x-model="formData.gpa"
                                placeholder="Contoh: 3.50"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Akreditasi
                            </label>
                            <input
                                type="text"
                                name="accreditation"
                                x-model="formData.accreditation"
                                placeholder="A / B / Unggul"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>
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
                            Simpan Pendidikan
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
