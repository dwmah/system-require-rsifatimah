<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Konfirmasi Pengajuan Lamaran - RSI Fatimah Banyuwangi</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #F6FAFF; color: #1E293B; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { background-color: #0179B5; color: #ffffff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; font-weight: 600; color: #0F172A; margin-bottom: 12px; }
        .info-box { background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .info-label { color: #64748B; }
        .info-val { font-weight: 600; color: #0F172A; text-align: right; }
        .footer { background-color: #F1F5F9; padding: 20px; text-align: center; font-size: 12px; color: #64748B; border-top: 1px solid #E2E8F0; }
        .button { display: inline-block; background-color: #0179B5; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RSI Fatimah Banyuwangi</h1>
            <p>Sistem Informasi Rekrutmen Karyawan</p>
        </div>
        <div class="content">
            <div class="greeting">Halo, {{ $application->user->name }}!</div>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Terima kasih telah mengajukan lamaran kerja di RSI Fatimah Banyuwangi. Dokumen dan data lamaran Anda telah berhasil dikirimkan ke Tim SDI (Sumber Daya Insani) untuk proses verifikasi kelengkapan berkas administrasi.
            </p>

            <div class="info-box">
                <div class="info-row"><span class="info-label">Nomor Registrasi:</span> <span class="info-val">{{ $application->user->registration_number ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Posisi Formasi:</span> <span class="info-val">{{ $application->jobVacancy->title }}</span></div>
                <div class="info-row"><span class="info-label">Unit/Departemen:</span> <span class="info-val">{{ $application->jobVacancy->department ?? 'Medis/Klinis' }}</span></div>
                <div class="info-row"><span class="info-label">Waktu Submit:</span> <span class="info-val">{{ $application->submitted_at ? $application->submitted_at->format('d F Y, H:i') . ' WIB' : '-' }}</span></div>
                <div class="info-row"><span class="info-label">Status Saat Ini:</span> <span class="info-val" style="color: #D97706;">Menunggu Verifikasi Berkas</span></div>
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Anda dapat memantau status validasi berkas dan perkembangan tahapan seleksi secara berkala melalui portal mandiri pelamar.
            </p>

            <div style="text-align: center;">
                <a href="{{ route('portal.home') }}" class="button">Buka Dashboard Pelamar</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} RSI Fatimah Banyuwangi. Seluruh hak cipta dilindungi.<br>
            Jl. Jember No. 25, Kalirejo, Kec. Kabat, Kabupaten Banyuwangi, Jawa Timur 68461
        </div>
    </div>
</body>
</html>
