<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Verifikasi Berkas Administrasi - RSI Fatimah Banyuwangi</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #F6FAFF; color: #1E293B; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { background-color: #0179B5; color: #ffffff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; font-weight: 600; color: #0F172A; margin-bottom: 12px; }
        .badge-passed { display: inline-block; background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 8px 16px; border-radius: 9999px; font-weight: 700; font-size: 14px; margin-bottom: 15px; }
        .badge-rejected { display: inline-block; background-color: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 8px 16px; border-radius: 9999px; font-weight: 700; font-size: 14px; margin-bottom: 15px; }
        .info-box { background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .reason-box { background-color: #FFF1F2; border-left: 4px solid #E11D48; padding: 14px; margin: 15px 0; border-radius: 0 8px 8px 0; }
        .footer { background-color: #F1F5F9; padding: 20px; text-align: center; font-size: 12px; color: #64748B; border-top: 1px solid #E2E8F0; }
        .button { display: inline-block; background-color: #0179B5; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RSI Fatimah Banyuwangi</h1>
            <p>Pengumuman Hasil Verifikasi Berkas Administrasi</p>
        </div>
        <div class="content">
            <div class="greeting">Halo, {{ $application->user->name }}!</div>

            @if($isPassed)
                <div style="text-align: center; margin: 15px 0;">
                    <span class="badge-passed">✓ ANDA LOLOS TAHAP ADMINISTRASI</span>
                </div>
                <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                    Selamat! Berdasarkan hasil evaluasi dan validasi kelengkapan dokumen persyaratan oleh Tim SDI RSI Fatimah Banyuwangi pada formasi <strong>{{ $application->jobVacancy->title }}</strong>, berkas Anda dinyatakan <strong>LENGKAP & MEMENUHI SYARAT</strong>.
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                    Anda berhak untuk melanjutkan ke tahapan seleksi berikutnya (Tes Tulis / Wawancara / MCU). Informasi jadwal dan instruksi pelaksanaan tes dapat diakses melalui portal pelamar.
                </p>
            @else
                <div style="text-align: center; margin: 15px 0;">
                    <span class="badge-rejected">✕ BELUM LOLOS SELEKSI ADMINISTRASI</span>
                </div>
                <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                    Terima kasih atas partisipasi dan antusiasme Anda untuk bergabung dengan RSI Fatimah Banyuwangi. Setelah dilakukan verifikasi dokumen pada formasi <strong>{{ $application->jobVacancy->title }}</strong>, mohon maaf berkas Anda dinyatakan <strong>BELUM MEMENUHI KETENTUAN</strong>.
                </p>

                @if($application->rejection_reason)
                    <div class="reason-box">
                        <strong style="color: #9F1239; font-size: 13px;">Catatan Alasan Penolakan:</strong>
                        <p style="margin: 5px 0 0; color: #881337; font-size: 13px; line-height: 1.5;">{{ $application->rejection_reason }}</p>
                    </div>
                @endif
            @endif

            <div style="text-align: center;">
                <a href="{{ route('portal.home') }}" class="button">Lihat Pengumuman di Portal</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} RSI Fatimah Banyuwangi. Seluruh hak cipta dilindungi.<br>
            Jl. Jember No. 25, Kalirejo, Kec. Kabat, Kabupaten Banyuwangi, Jawa Timur 68461
        </div>
    </div>
</body>
</html>
