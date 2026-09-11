<x-layouts.public title="Design System & Component Preview" active-menu="lowongan">
    <div class="space-y-12 max-w-5xl">
        {{-- Page Header --}}
        <div class="border-b border-slate-200 pb-5">
            <div class="flex items-center gap-2 mb-2">
                <x-ui.badge variant="info" dot>Development Only</x-ui.badge>
                <x-ui.badge variant="neutral">Tailwind CSS v4</x-ui.badge>
                <x-ui.badge variant="success">Alpine.js Ready</x-ui.badge>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                Design System & Component Foundation
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Sistem Rekrutmen SDM RSI Fatimah Banyuwangi &bull; Standar Visual Hospital Information System
            </p>
        </div>

        {{-- 1. Design Tokens: Colors & Typography --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">1. Design Tokens & Palette</h2>
                <p class="text-xs text-slate-500">Karakter visual institusi rumah sakit: dominan putih, aksen biru RSI Fatimah, bersih dan terpercaya.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-rsi-700 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Primary Blue</p>
                    <p class="text-[11px] text-slate-400 font-mono">--color-rsi-700</p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-rsi-800 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Primary Dark</p>
                    <p class="text-[11px] text-slate-400 font-mono">--color-rsi-800</p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-rsi-100 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Primary Light</p>
                    <p class="text-[11px] text-slate-400 font-mono">--color-rsi-100</p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-emerald-600 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Success</p>
                    <p class="text-[11px] text-slate-400 font-mono">#16a34a</p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-amber-500 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Warning</p>
                    <p class="text-[11px] text-slate-400 font-mono">#f59e0b</p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div class="h-10 rounded-lg bg-red-600 mb-2 ring-1 ring-black/5"></div>
                    <p class="text-xs font-semibold text-slate-800">Danger</p>
                    <p class="text-[11px] text-slate-400 font-mono">#dc2626</p>
                </div>
            </div>
        </section>

        {{-- 2. Button Components --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">2. Button Components</h2>
                <p class="text-xs text-slate-500">Mendukung tag button, anchor link (href), berbagai ukuran, dan accessibility focus.</p>
            </div>

            <x-ui.card>
                <div class="space-y-5">
                    {{-- Variants --}}
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Varian Tombol</p>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-ui.button variant="primary">Primary Action</x-ui.button>
                            <x-ui.button variant="secondary">Secondary Action</x-ui.button>
                            <x-ui.button variant="outline">Outline Action</x-ui.button>
                            <x-ui.button variant="danger">Danger Action</x-ui.button>
                            <x-ui.button variant="ghost">Ghost Action</x-ui.button>
                        </div>
                    </div>

                    {{-- Sizes --}}
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Ukuran Tombol</p>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-ui.button variant="primary" size="sm">Small (sm)</x-ui.button>
                            <x-ui.button variant="primary" size="md">Medium (md)</x-ui.button>
                            <x-ui.button variant="primary" size="lg">Large (lg)</x-ui.button>
                        </div>
                    </div>

                    {{-- Link & Disabled --}}
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">State & Link Element</p>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-ui.button href="/lowongan" variant="primary">Render Sebagai Link (&lt;a&gt;)</x-ui.button>
                            <x-ui.button variant="primary" :disabled="true">Button Disabled</x-ui.button>
                            <x-ui.button href="/lowongan" variant="outline" :disabled="true">Link Disabled</x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </section>

        {{-- 3. Badges --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">3. Badge & Status Components</h2>
                <p class="text-xs text-slate-500">Digunakan untuk visualisasi status rekrutmen, berkas, dan tahapan pelamar.</p>
            </div>

            <x-ui.card>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Badge Standar</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="neutral">Draft</x-ui.badge>
                            <x-ui.badge variant="info">Diproses</x-ui.badge>
                            <x-ui.badge variant="success">Lolos / Diterima</x-ui.badge>
                            <x-ui.badge variant="warning">Segera Ditutup</x-ui.badge>
                            <x-ui.badge variant="danger">Tidak Lolos</x-ui.badge>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Badge dengan Dot Indikator</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="success" dot>Lowongan Dibuka</x-ui.badge>
                            <x-ui.badge variant="info" dot>Menunggu Evaluasi</x-ui.badge>
                            <x-ui.badge variant="warning" dot>Sisa 2 Hari</x-ui.badge>
                            <x-ui.badge variant="danger" dot>Persyaratan Kurang</x-ui.badge>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </section>

        {{-- 4. Alerts --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">4. Alert Components</h2>
                <p class="text-xs text-slate-500">Pemberitahuan kontekstual dengan dukungan penutupan interaktif via Alpine.js.</p>
            </div>

            <div class="space-y-3">
                <x-ui.alert type="info" title="Informasi Pendaftaran SDM">
                    Pendaftaran lowongan formasi Perawat IGD akan ditutup pada tanggal 20 Oktober 2026 pukul 23.59 WIB.
                </x-ui.alert>

                <x-ui.alert type="success" title="Dokumen Berhasil Diverifikasi" dismissible>
                    Seluruh berkas persyaratan ijazah dan STR Anda telah berhasil diverifikasi oleh tim SDM RSI Fatimah.
                </x-ui.alert>

                <x-ui.alert type="warning" title="Peringatan Batas Waktu">
                    Mohon segera lengkapi berkas Surat Bebas Narkoba sebelum tahap Medical Check Up dimulai.
                </x-ui.alert>

                <x-ui.alert type="danger" title="Terjadi Kesalahan Berkas" dismissible>
                    Format file yang diunggah harus berupa PDF dengan ukuran maksimal 2 MB.
                </x-ui.alert>
            </div>
        </section>

        {{-- 5. Card Component --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">5. Card Components</h2>
                <p class="text-xs text-slate-500">Komponen kontainer modular dengan header, action slot, dan footer.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-ui.card
                    title="Perawat Instalasi Gawat Darurat"
                    subtitle="Kode Lowongan: POS-2026-IGD-01"
                >
                    <x-slot:header>
                        <x-ui.badge variant="success" dot>Dibuka</x-ui.badge>
                    </x-slot:header>

                    <div class="space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Unit Penempatan:</span>
                            <span class="font-medium text-slate-900">Instalasi Gawat Darurat</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Kuota Formasi:</span>
                            <span class="font-medium text-slate-900">5 Orang</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500">Batas Pendaftaran:</span>
                            <span class="font-medium text-slate-900">20 Oktober 2026</span>
                        </div>
                    </div>

                    <x-slot:footer>
                        <span class="text-xs text-slate-400">RSI Fatimah Banyuwangi</span>
                        <x-ui.button variant="primary" size="sm">Lihat Detail</x-ui.button>
                    </x-slot:footer>
                </x-ui.card>

                <x-ui.card title="Status Pengajuan Lamaran">
                    <div class="space-y-4">
                        <x-ui.progress-bar :value="60" label="Kemajuan Seleksi" />
                        <div class="p-3 rounded-lg bg-slate-50 text-xs text-slate-600 space-y-1">
                            <p class="font-semibold text-slate-800">Tahap Terakhir: Ujian Tulis & Skill Test</p>
                            <p class="text-slate-500">Hasil evaluasi sedang direview oleh dewan penguji SDM.</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </section>

        {{-- 6. Form Components --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">6. Form Components</h2>
                <p class="text-xs text-slate-500">Form input terstandarisasi dengan label, placeholder, helper, dan integrasi error state.</p>
            </div>

            <x-ui.card title="Formulir Data Diri Pelamar">
                <form class="space-y-4" onsubmit="event.preventDefault()">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form.input
                            name="full_name"
                            label="Nama Lengkap"
                            placeholder="Sesuai ijazah / KTP"
                            value="Ahmad Fauzi, S.Kep., Ns."
                            required
                        />

                        <x-form.input
                            name="nik"
                            label="Nomor Induk Kependudukan (NIK)"
                            placeholder="16 digit NIK KTP"
                            helper="NIK harus sesuai dengan KTP elektronik asli"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-form.select
                            name="education_level"
                            label="Tingkat Pendidikan Terakhir"
                            :options="[
                                'D3' => 'D3 Keperawatan',
                                'S1' => 'S1 / Profesi Ners',
                                'S2' => 'S2 Keperawatan Klinis',
                            ]"
                            selected="S1"
                            required
                        />

                        <x-form.input
                            name="phone"
                            label="Nomor WhatsApp Aktif"
                            placeholder="081234567890"
                            helper="Akan digunakan untuk notifikasi jadwal ujian"
                            required
                        />
                    </div>

                    <x-form.textarea
                        name="address_ktp"
                        label="Alamat KTP Lengkap"
                        placeholder="Tuliskan nama jalan, RT/RW, kelurahan, kecamatan, dan kabupaten..."
                        rows="3"
                        required
                    />

                    <x-form.file-input
                        name="document_str"
                        label="Unggah Surat Tanda Registrasi (STR)"
                        allowed-mimes="PDF"
                        :max-size-kb="2048"
                        helper="Pastikan STR berstatus aktif dari KTKI/Kemenkes"
                        required
                    />

                    <x-form.checkbox
                        name="confirm_data"
                        label="Saya menyatakan seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan."
                        description="Data palsu dapat membatalkan keikutsertaan seleksi secara otomatis."
                        checked
                        required
                    />

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2.5">
                        <x-ui.button variant="secondary">Reset</x-ui.button>
                        <x-ui.button variant="primary">Simpan Formulir</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </section>

        {{-- 7. Breadcrumbs & Empty State --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">7. Breadcrumb & Empty State</h2>
                <p class="text-xs text-slate-500">Navigasi hirarki halaman dan visualisasi kondisi ketika data belum tersedia.</p>
            </div>

            <x-ui.card>
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Contoh Breadcrumb</p>
                        <x-ui.breadcrumb :items="[
                            ['label' => 'Lowongan', 'url' => '/lowongan'],
                            ['label' => 'Keperawatan', 'url' => '/lowongan?unit=keperawatan'],
                            ['label' => 'Perawat IGD']
                        ]" />
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-600 mb-2.5 uppercase tracking-wider">Contoh Empty State</p>
                        <x-ui.empty-state
                            title="Belum ada berkas yang diunggah"
                            description="Silakan unggah dokumen persyaratan wajib untuk melanjutkan ke tahap verifikasi berkas oleh panitia rekrutmen."
                        >
                            <x-ui.button variant="outline" size="sm">Unggah Dokumen Pertama</x-ui.button>
                        </x-ui.empty-state>
                    </div>
                </div>
            </x-ui.card>
        </section>

        {{-- 8. Visual Previews of the 3 Layouts --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">8. Preview Shell Layout</h2>
                <p class="text-xs text-slate-500">Verifikasi struktur visual untuk Public Layout, Applicant Portal, dan Auth Layout.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Public Layout Preview Card --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">Public Layout</span>
                        <x-ui.badge variant="info" size="sm">x-layouts.public</x-ui.badge>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Layout dengan Topbar RSI Fatimah, Left Sidebar publik (Lowongan, Panduan Alur, Syarat), Main Content fluid, dan Footer instansi.
                    </p>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100 text-[11px] text-slate-600 font-mono">
                        Header &rarr; Sidebar (Kiri) + Content &rarr; Footer
                    </div>
                </div>

                {{-- Applicant Portal Layout Preview Card --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">Applicant Portal</span>
                        <x-ui.badge variant="success" size="sm">x-layouts.applicant</x-ui.badge>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Layout pasca login dengan Topbar profil pelamar, Left Sidebar portal (Dashboard, Profil, Lamaran, Notifikasi), dan status akun.
                    </p>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100 text-[11px] text-slate-600 font-mono">
                        Topbar Profil &rarr; Sidebar Pelamar &rarr; Footer
                    </div>
                </div>

                {{-- Auth Layout Preview Card --}}
                <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">Auth Layout</span>
                        <x-ui.badge variant="neutral" size="sm">x-layouts.auth</x-ui.badge>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Layout bersih tanpa sidebar, berpusat pada Card Login / Register dengan tombol navigasi kembali ke beranda.
                    </p>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100 text-[11px] text-slate-600 font-mono">
                        Header Minimal &rarr; Centered Auth Card &rarr; Footer
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public>
