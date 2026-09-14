<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pembaruan Tahapan Seleksi - RSI Fatimah Banyuwangi</title>
    <style>
        body { font-family: 'Tahoma', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #F6FAFF; color: #1E293B; margin: 0; padding: 0; font-size: 15px; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { background-color: #0179B5; color: #ffffff; padding: 24px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; font-weight: 600; color: #0F172A; margin-bottom: 12px; }
        .stage-box { background-color: #F0F9FF; border: 1px solid #BAE6FD; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .footer { background-color: #F1F5F9; padding: 20px; text-align: center; font-size: 12px; color: #64748B; border-top: 1px solid #E2E8F0; }
        .button { display: inline-block; background-color: #0179B5; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RSI Fatimah Banyuwangi</h1>
            <p>Pemberitahuan Tahapan Seleksi Karyawan</p>
        </div>
        <div class="content">
            <div class="greeting">Halo, {{ $application->user->name }}!</div>
            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Terdapat pembaruan informasi mengenai tahapan seleksi Anda pada posisi <strong>{{ $application->jobVacancy->title }}</strong>.
            </p>

            <div class="stage-box">
                <div style="font-size: 13px; color: #0369A1; font-weight: 600;">Tahapan Seleksi:</div>
                <div style="font-size: 16px; color: #0C4A6E; font-weight: 700; margin-top: 4px;">{{ $stageName }}</div>
                @if(!empty($stageNote))
                    <div style="margin-top: 10px; font-size: 13px; color: #334155; line-height: 1.5; border-top: 1px dashed #BAE6FD; padding-top: 8px;">
                        <strong>Catatan/Instruksi Tim SDI:</strong><br>
                        {{ $stageNote }}
                    </div>
                @endif
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #475569;">
                Harap memantau jadwal dan mempersiapkan dokumen/kelengkapan yang dibutuhkan sesuai instruksi di atas.
            </p>

            <div style="text-align: center;">
                <a href="{{ route('portal.home') }}" class="button">Buka Portal Pelamar</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} RSI Fatimah Banyuwangi. Seluruh hak cipta dilindungi.<br>
            Jl. Jember No. 25, Kalirejo, Kec. Kabat, Kabupaten Banyuwangi, Jawa Timur 68461
        </div>
    </div>
</body>
</html>
