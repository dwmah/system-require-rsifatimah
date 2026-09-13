@props([
    'licenses' => collect(),
])

<div
    class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 sm:p-7"
    x-data="{
        showModal: false,
        isEdit: false,
        modalTitle: 'Tambah Lisensi / STR',
        formAction: '{{ route('profile.licenses.store') }}',
        formData: {
            id: null,
            license_type: '',
            license_number: '',
            issued_at: '',
            expires_at: ''
        },
        openCreate() {
            this.isEdit = false;
            this.modalTitle = 'Tambah Lisensi / STR';
            this.formAction = '{{ route('profile.licenses.store') }}';
            this.formData = {
                id: null,
                license_type: '',
                license_number: '',
                issued_at: '',
                expires_at: ''
            };
            this.showModal = true;
        },
        openEdit(item) {
            this.isEdit = true;
            this.modalTitle = 'Edit Lisensi / STR';
            this.formAction = '/portal/profil/lisensi/' + item.id;
            this.formData = {
                id: item.id,
                license_type: item.license_type,
                license_number: item.license_number,
                issued_at: item.issued_at ? item.issued_at.substring(0, 10) : '',
                expires_at: item.expires_at ? item.expires_at.substring(0, 10) : ''
            };
            this.showModal = true;
        }
    }"
>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-slate-100 gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-base font-bold text-slate-900">Surat Tanda Registrasi (STR) / Lisensi Profesi</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    OPSIONAL / SESUAI FORMASI
                </span>
            </div>
            <p class="text-xs text-slate-500">Khusus tenaga kesehatan (Dokter, Perawat, Bidan, Apoteker, Nakes lainnya).</p>
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
                <span>Tambah Lisensi / STR</span>
            </button>
        </div>
    </div>

    {{-- Cards List --}}
    @if ($licenses->isEmpty())
        <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200 text-slate-400">
            <p class="text-xs font-medium text-slate-600">Belum ada STR atau lisensi profesi yang dicatat</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Jika formasi yang Anda tuju membutuhkan STR, silakan tambahkan data lisensi Anda.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($licenses as $lic)
                <div class="p-4 bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-sky-50 text-sky-800">
                                {{ $lic->license_type }}
                            </span>
                            <span class="text-xs font-mono font-bold text-slate-900">No: {{ $lic->license_number }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 pt-0.5">
                            @if ($lic->issued_at)
                                <span>Terbit: <strong>{{ $lic->issued_at->format('d M Y') }}</strong></span>
                            @endif
                            @if ($lic->expires_at)
                                <span>Berlaku s/d: <strong>{{ $lic->expires_at->format('d M Y') }}</strong></span>
                            @else
                                <span class="text-emerald-700 font-semibold">Berlaku Seumur Hidup</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center">
                        <button
                            type="button"
                            @click="openEdit({{ json_encode($lic) }})"
                            class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-rsi-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                        >
                            Edit
                        </button>
                        <form method="POST" action="{{ route('profile.licenses.destroy', $lic) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data lisensi ini?')">
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

    {{-- Modal Tambah/Edit Lisensi --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
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
                    <h3 class="text-base font-bold text-slate-900" x-text="modalTitle"></h3>
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Jenis Lisensi / STR <span class="text-red-500 font-bold">*</span>
                        </label>
                        <select
                            name="license_type"
                            x-model="formData.license_type"
                            required
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 bg-white text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                        >
                            <option value="">Pilih Jenis Lisensi / STR</option>
                            <option value="STR Perawat">STR Perawat</option>
                            <option value="STR Bidan">STR Bidan</option>
                            <option value="STR Dokter Umum">STR Dokter Umum</option>
                            <option value="STR Dokter Spesialis">STR Dokter Spesialis</option>
                            <option value="STRA (Apoteker)">STRA (Apoteker)</option>
                            <option value="STR Tenaga Teknis Kefarmasian">STR Tenaga Teknis Kefarmasian (TTK)</option>
                            <option value="STR Radiografer">STR Radiografer</option>
                            <option value="STR Ahli Teknologi Laboratorium Medik">STR ATLM / Analis Kesehatan</option>
                            <option value="STR Fisioterapis">STR Fisioterapis</option>
                            <option value="STR Perekam Medis">STR Perekam Medis (RMIK)</option>
                            <option value="STR Ahli Gizi / Nutrisionis">STR Ahli Gizi / Nutrisionis</option>
                            <option value="SIP (Surat Izin Praktik)">SIP (Surat Izin Praktik)</option>
                            <option value="Lainnya">Lisensi Medis Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor Registrasi / STR <span class="text-red-500 font-bold">*</span>
                        </label>
                        <input
                            type="text"
                            name="license_number"
                            x-model="formData.license_number"
                            placeholder="Contoh: 1234567890123456"
                            required
                            class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 font-mono text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Tanggal Terbit
                            </label>
                            <input
                                type="date"
                                name="issued_at"
                                x-model="formData.issued_at"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Berlaku Sampai
                            </label>
                            <input
                                type="date"
                                name="expires_at"
                                x-model="formData.expires_at"
                                :disabled="formData.is_lifetime"
                                class="w-full rounded-lg text-sm py-2 px-3.5 border border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100 focus:outline-hidden focus:ring-2 disabled:bg-slate-100 disabled:text-slate-400"
                            >
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            type="checkbox"
                            name="is_lifetime"
                            id="is_lifetime"
                            x-model="formData.is_lifetime"
                            value="1"
                            @change="if(formData.is_lifetime) formData.expires_at = ''"
                            class="rounded border-slate-300 text-rsi-700 focus:ring-rsi-600 h-4 w-4"
                        >
                        <label for="is_lifetime" class="text-xs font-medium text-slate-700">
                            Berlaku Seumur Hidup (STR Peraturan Terbaru)
                        </label>
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
                            Simpan STR / Lisensi
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
