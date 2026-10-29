<x-layouts.public :title="$guide->title ?? 'Panduan Persyaratan Pendaftaran'" active-menu="persyaratan">
    <div class="space-y-6 max-w-4xl">
        {{-- Breadcrumb --}}
        <x-ui.breadcrumb :items="[
            ['label' => 'Panduan'],
            ['label' => 'Persyaratan Pendaftaran']
        ]" />

        {{-- Section Header --}}
        <div class="border-b border-slate-200 pb-5">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ $guide->title ?? 'Persyaratan & Ketentuan Pendaftaran' }}
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $guide->subtitle ?? 'Panduan kualifikasi umum, kelengkapan berkas digital, dan tata tertib seleksi SDM RSI Fatimah Banyuwangi.' }}
            </p>
        </div>

        <div class="space-y-4">
            @php
                $items = $guide?->content ?? [];
            @endphp

            @forelse($items as $item)
                <x-ui.card :title="$item['title'] ?? ''">
                    @php
                        $desc = $item['description'] ?? '';
                        $lines = array_filter(array_map('trim', explode("\n", (string) $desc)));
                    @endphp
                    @if(count($lines) > 1)
                        <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                            @foreach($lines as $line)
                                <li>{!! $line !!}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-xs text-slate-600 leading-relaxed space-y-1.5">
                            <p>{!! nl2br($desc) !!}</p>
                        </div>
                    @endif
                </x-ui.card>
            @empty
                {{-- Fallback Default Cards --}}
                <x-ui.card title="1. Persyaratan Umum Pelamar">
                    <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li>Warga Negara Indonesia (WNI) yang berakhlak mulia.</li>
                        <li>Mampu membaca Al-Qur'an dengan baik dan benar (nilai tambah).</li>
                        <li>Sehat jasmani dan rohani, serta bebas dari narkoba (NAPZA).</li>
                        <li>Berkelakuan baik dan tidak pernah dipidana (dibuktikan dengan SKCK).</li>
                        <li>Bersedia mematuhi peraturan kekaryawanan dan nilai-nilai Islami RSI Fatimah Banyuwangi.</li>
                    </ul>
                </x-ui.card>

                <x-ui.card title="2. Ketentuan Akun & Data Pelamar">
                    <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li>Wajib menggunakan alamat email dan nomor WhatsApp pribadi yang aktif.</li>
                        <li><strong class="text-slate-800">1 NIK = 1 Akun</strong>: Nomor Induk Kependudukan hanya dapat didaftarkan untuk satu akun.</li>
                        <li>Pelamar bertanggung jawab penuh atas kebenaran seluruh data yang diisi.</li>
                        <li>Pemberian data palsu akan mengakibatkan <strong class="text-rose-600">diskualifikasi otomatis</strong> pada semua tahapan.</li>
                    </ul>
                </x-ui.card>

                <x-ui.card title="3. Ketentuan Dokumen Pendaftaran">
                    <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li>Berkas diunggah dalam format <strong class="text-slate-800">PDF (.pdf)</strong> dengan ukuran maksimal <strong class="text-slate-800">2 MB</strong> per file.</li>
                        <li>Hasil scan dokumen asli harus tegak lurus, terbaca jelas, dan tidak terpotong.</li>
                        <li><strong class="text-slate-800">Dokumen Wajib:</strong> Surat Lamaran, CV terbaru, Ijazah Asli, Transkrip Nilai, e-KTP, dan Kartu Keluarga (KK).</li>
                        <li><strong class="text-slate-800">Tenaga Kesehatan:</strong> Wajib melampirkan <strong class="text-slate-800">STR aktif</strong> yang masih berlaku dan terdaftar resmi.</li>
                        <li><strong class="text-slate-800">Dokumen Pendukung:</strong> Sertifikat pelatihan/keahlian atau berkas lain dapat dilampirkan sesuai persyaratan lowongan.</li>
                    </ul>
                </x-ui.card>

                <x-ui.card title="4. Ketentuan Pengajuan Lamaran">
                    <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li>Setiap pelamar hanya dapat memilih <strong class="text-slate-800">1 (satu) formasi lowongan</strong> dalam satu periode seleksi.</li>
                        <li>Setelah lamaran dikirim (*submit*), pilihan formasi dan berkas <strong class="text-rose-600">terkunci dan tidak dapat diubah</strong>.</li>
                        <li>Pelamar akan memperoleh <strong class="text-slate-800">Nomor Registrasi Lamaran</strong> untuk memantau status seleksi di menu <span class="font-medium text-slate-700">Lamaran Saya</span>.</li>
                    </ul>
                </x-ui.card>

                <x-ui.card title="5. Persyaratan Khusus Formasi Lowongan">
                    <div class="text-xs text-slate-600 leading-relaxed space-y-1.5">
                        <p>
                            Setiap formasi lowongan memiliki kualifikasi khusus (pendidikan minimal, IPK, batas usia, dan keahlian teknis) sesuai kebutuhan unit kerja.
                        </p>
                        <p>
                            Rincian kualifikasi masing-masing posisi dapat dilihat langsung pada 
                            <a href="{{ route('vacancies.index') }}" class="text-rsi-700 font-semibold hover:underline inline-flex items-center gap-1">
                                Halaman Lowongan Pekerjaan &rarr;
                            </a>
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card title="6. Hal yang Perlu Diperhatikan">
                    <ul class="list-disc list-outside ml-4 space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li>Berkas lamaran yang tidak lengkap, tidak terbaca, atau melewati batas waktu pendaftaran tidak akan diproses.</li>
                        <li>Keputusan Panitia Seleksi Rekrutmen bersifat final, mengikat, dan tidak dapat diganggu gugat.</li>
                        <li>Seluruh proses rekrutmen pegawai RSI Fatimah Banyuwangi <strong class="text-slate-900">GRATIS (tidak dipungut biaya apapun)</strong>. Waspadai segala bentuk penipuan.</li>
                    </ul>
                </x-ui.card>
            @endforelse
        </div>
    </div>
</x-layouts.public>
