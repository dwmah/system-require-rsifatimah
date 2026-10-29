<x-layouts.admin :title="'Edit Panduan: ' . $guide->title">
    <div class="space-y-6"
         x-data="{
            items: {{ \Illuminate\Support\Js::from($guide->content ?? []) }},
            init() {
                if (!Array.isArray(this.items) || this.items.length === 0) {
                    this.items = [{ step: 1, title: '', description: '' }];
                }
            },
            addItem() {
                this.items.push({
                    step: this.items.length + 1,
                    title: '',
                    description: ''
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.reindexSteps();
                } else {
                    this.items[0] = { step: 1, title: '', description: '' };
                }
            },
            reindexSteps() {
                this.items.forEach((item, idx) => {
                    item.step = idx + 1;
                });
            },
            moveUp(index) {
                if (index > 0) {
                    const temp = this.items[index];
                    this.items[index] = this.items[index - 1];
                    this.items[index - 1] = temp;
                    this.reindexSteps();
                }
            },
            moveDown(index) {
                if (index < this.items.length - 1) {
                    const temp = this.items[index];
                    this.items[index] = this.items[index + 1];
                    this.items[index + 1] = temp;
                    this.reindexSteps();
                }
            }
         }">

        <!-- Breadcrumb & Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('admin.guides.index') }}" class="hover:text-[#0179B5]">Kelola Panduan</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Edit Konten</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Panduan: {{ $guide->title }}</h1>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $isFlow = ($guide->type instanceof \App\Enums\RecruitmentGuideType) ? $guide->type === \App\Enums\RecruitmentGuideType::FLOW : $guide->type === 'flow';
                    $publicUrl = $isFlow ? route('guides.flow') : route('guides.requirements');
                @endphp
                <a href="{{ $publicUrl }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-[#0179B5] bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span>Pratinjau Live Portal</span>
                </a>
                <a href="{{ route('admin.guides.index') }}"
                   class="inline-flex items-center gap-1 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Form Edit Panduan -->
        <form method="POST" action="{{ route('admin.guides.update', $guide) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Kartu Metadata Panduan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Informasi Utama Panduan</h2>
                    <span class="text-xs text-slate-400">Slug: <code class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded">{{ $guide->slug }}</code></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Halaman Panduan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $guide->title) }}" required
                               class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                        @error('title')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe / Kategori Panduan <span class="text-rose-500">*</span></label>
                        @php
                            $currentType = ($guide->type instanceof \App\Enums\RecruitmentGuideType) ? $guide->type->value : $guide->type;
                        @endphp
                        <select name="type" required
                                class="w-full h-11 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                            <option value="flow" {{ old('type', $currentType) === 'flow' ? 'selected' : '' }}>Alur Pendaftaran (flow)</option>
                            <option value="requirements" {{ old('type', $currentType) === 'requirements' ? 'selected' : '' }}>Persyaratan Umum (requirements)</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subjudul / Deskripsi Header</label>
                    <textarea name="subtitle" rows="2"
                              class="w-full p-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">{{ old('subtitle', $guide->subtitle) }}</textarea>
                    @error('subtitle')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center gap-6 pt-2 border-t border-slate-100">
                    <div class="w-40">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan Prioritas</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $guide->sort_order) }}" min="1"
                               class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5] focus:bg-white">
                    </div>

                    <div class="flex items-center pt-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active_checkbox" value="1" {{ old('is_active_checkbox', $guide->is_active) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#0179B5] focus:ring-[#0179B5]">
                            <span class="text-xs font-semibold text-slate-700">Status Panduan Aktif di Portal Publik</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Kartu Item / Langkah Panduan Dinamis -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Item Poin / Langkah Panduan</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola langkah urutan alur pendaftaran atau poin-poin persyaratan secara berurutan.</p>
                    </div>

                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Tambah Poin / Langkah</span>
                    </button>
                </div>

                <!-- Daftar Item Repeater -->
                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 sm:p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-3 relative group hover:border-[#0179B5]/40 transition-colors">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-200/70 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#0179B5] text-white text-xs font-bold flex items-center justify-center shadow-xs"
                                          x-text="item.step || (index + 1)"></span>
                                    <span class="text-xs font-bold text-slate-700" x-text="'Langkah / Poin #' + (item.step || (index + 1))"></span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <!-- Move Up -->
                                    <button type="button" @click="moveUp(index)" :disabled="index === 0"
                                            title="Geser ke atas"
                                            class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    </button>
                                    <!-- Move Down -->
                                    <button type="button" @click="moveDown(index)" :disabled="index === items.length - 1"
                                            title="Geser ke bawah"
                                            class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <!-- Delete Item -->
                                    <button type="button" @click="removeItem(index)"
                                            title="Hapus langkah"
                                            class="p-1 text-rose-400 hover:text-rose-600 ml-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <input type="hidden" :name="'items[' + index + '][step]'" :value="item.step || (index + 1)">

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Poin / Tahapan <span class="text-rose-500">*</span></label>
                                    <input type="text" :name="'items[' + index + '][title]'" x-model="item.title" required
                                           placeholder="Contoh: Registrasi Akun Pelamar"
                                           class="w-full h-10 px-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi & Rincian Ketentuan</label>
                                    <textarea :name="'items[' + index + '][description]'" x-model="item.description" rows="3"
                                              placeholder="Tuliskan petunjuk lengkap atau ketentuan terkait poin ini..."
                                              class="w-full p-3 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:border-[#0179B5]"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-3">
                    <button type="button" @click="addItem()"
                            class="w-full py-2.5 border-2 border-dashed border-slate-200 hover:border-[#0179B5] text-slate-600 hover:text-[#0179B5] text-xs font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Tambah Poin / Langkah Baru</span>
                    </button>
                </div>
            </div>

            <!-- Tombol Aksi Simpan -->
            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('admin.guides.index') }}"
                   class="px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                    Batalkan
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-[#0179B5] hover:bg-[#016699] text-white text-sm font-semibold rounded-xl shadow-xs transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Simpan Perubahan Panduan</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
