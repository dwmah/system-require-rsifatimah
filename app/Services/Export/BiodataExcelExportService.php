<?php

namespace App\Services\Export;

use App\Enums\JobApplicationStatus;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class BiodataExcelExportService
{
    /**
     * Generate and download an .xlsx file of all applicants' complete biodata who have applied.
     */
    public function exportApplicants(
        ?int $vacancyId = null,
        ?string $status = null,
        ?string $search = null,
        ?int $recruitmentPeriodId = null
    ): BinaryFileResponse {
        $query = JobApplication::with([
            'user.applicantProfile.educations' => function ($q) {
                $q->orderBy('graduation_year', 'desc');
            },
            'jobVacancy',
            'recruitmentPeriod',
        ]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('registration_number', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($recruitmentPeriodId) {
            $query->where('recruitment_period_id', $recruitmentPeriodId);
        }

        if ($vacancyId) {
            $query->where('job_vacancy_id', $vacancyId);
        }

        if (! empty($status)) {
            $query->where('status', $status);
        } else {
            // Ambil semua yang sudah melamar (bukan draft)
            $query->where('status', '!=', JobApplicationStatus::DRAFT);
        }

        $applications = $query->latest('submitted_at')->get();

        return $this->generateXlsx($applications);
    }

    /**
     * Generate and download an .xlsx file of all accepted applicants' complete biodata.
     */
    public function exportAcceptedApplicants(?int $vacancyId = null): BinaryFileResponse
    {
        return $this->exportApplicants(
            vacancyId: $vacancyId,
            status: JobApplicationStatus::ACCEPTED->value
        );
    }

    /**
     * Build the OpenXML Spreadsheet (.xlsx) file and return a BinaryFileResponse.
     *
     * @param  Collection<int, JobApplication>  $applications
     */
    public function generateXlsx(Collection $applications): BinaryFileResponse
    {
        $filename = 'Biodata_Pelamar_RSI_Fatimah_'.date('Ymd_His').'.xlsx';
        $tempDir = storage_path('app/temp');

        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir.'/'.uniqid('export_xlsx_').'.xlsx';

        $zip = new ZipArchive;
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal menginisialisasi berkas Excel (.xlsx).');
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>'
            .'<sheet name="Biodata Pelamar" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // 5. xl/styles.xml
        $styles = $this->buildStylesXml();
        $zip->addFromString('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $sheet1 = $this->buildWorksheetXml($applications);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1);

        $zip->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Styles configuration (Fonts, Fills, Borders, Cell Formats).
     */
    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="4">'
            // 0: Regular 10pt
            .'<font><sz val="10"/><name val="Calibri"/><color rgb="FF1E293B"/></font>'
            // 1: Title Bold 14pt Primary Blue
            .'<font><b/><sz val="14"/><name val="Calibri"/><color rgb="FF000000"/></font>'
            // 2: Header Bold 10pt White
            .'<font><b/><sz val="10"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>'
            // 3: Subtitle Italic 9pt Slate
            .'<font><i/><sz val="9"/><name val="Calibri"/><color rgb="FF64748B"/></font>'
            .'</fonts>'
            .'<fills count="5">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            // 2: Header Fill #0179B5 (RSI Fatimah Blue)
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0179B5"/></patternFill></fill>'
            // 3: Zebra Row Fill #F8FAFC
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>'
            // 4: Highlight Success #D1FAE5
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFD1FAE5"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            // 0: None
            .'<border><left/><right/><top/><bottom/></border>'
            // 1: Thin Gray
            .'<border>'
            .'<left style="thin"><color rgb="FFE2E8F0"/></left>'
            .'<right style="thin"><color rgb="FFE2E8F0"/></right>'
            .'<top style="thin"><color rgb="FFE2E8F0"/></top>'
            .'<bottom style="thin"><color rgb="FFE2E8F0"/></bottom>'
            .'</border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="6">'
            // 0: Default Normal
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>'
            // 1: Title
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            // 2: Subtitle
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            // 3: Header Row (Bold, White text, Blue Fill, Center)
            .'<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 4: Regular Center Aligned
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 5: Zebra Row Center Aligned
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }

    /**
     * Build the Worksheet XML with header columns and candidate rows.
     *
     * @param  Collection<int, JobApplication>  $applications
     */
    protected function buildWorksheetXml(Collection $applications): string
    {
        $headers = [
            'No.',
            'No. Registrasi',
            'No. Lamaran',
            'Nama Lengkap',
            'NIK (KTP)',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Usia',
            'Email',
            'No. WhatsApp / HP',
            'Alamat KTP',
            'Alamat Domisili',
            'Status Domisili',
            'Formasi Dilamar',
            'Departemen / Unit',
            'Pendidikan Terakhir',
            'Institusi / Universitas',
            'Program Studi',
            'Tahun Lulus',
            'IPK / Nilai',
            'Akreditasi',
            'Tanggal Melamar',
            'Status Lamaran',
            'Status Akun',
        ];

        $columnWidths = [
            1 => 6,   // No
            2 => 18,  // No. Registrasi
            3 => 18,  // No. Lamaran
            4 => 26,  // Nama Lengkap
            5 => 20,  // NIK
            6 => 14,  // Jenis Kelamin
            7 => 16,  // Tempat Lahir
            8 => 15,  // Tanggal Lahir
            9 => 10,  // Usia
            10 => 26, // Email
            11 => 18, // No. HP
            12 => 32, // Alamat KTP
            13 => 32, // Alamat Domisili
            14 => 16, // Status Domisili
            15 => 24, // Formasi Dilamar
            16 => 20, // Departemen
            17 => 18, // Pendidikan Terakhir
            18 => 28, // Institusi
            19 => 24, // Program Studi
            20 => 12, // Tahun Lulus
            21 => 12, // IPK
            22 => 12, // Akreditasi
            23 => 18, // Tanggal Melamar
            24 => 20, // Status Lamaran
            25 => 16, // Status Akun
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";

        // Column widths
        $xml .= '<cols>'."\n";
        foreach ($columnWidths as $colIndex => $width) {
            $xml .= '<col min="'.$colIndex.'" max="'.$colIndex.'" width="'.$width.'" customWidth="1"/>'."\n";
        }
        $xml .= '</cols>'."\n";

        $xml .= '<sheetData>'."\n";

        // Row 1: Title
        $xml .= '<row r="1" ht="26" customHeight="1">'."\n";
        $xml .= '<c r="A1" t="inlineStr" s="1"><is><t>RSI FATIMAH BANYUWANGI - REKAPITULASI DATA BIODATA PELAMAR</t></is></c>'."\n";
        $xml .= '</row>'."\n";

        // Row 2: Subtitle
        $exportTime = Carbon::now()->locale('id')->translatedFormat('d F Y, H:i').' WIB';
        $xml .= '<row r="2" ht="18" customHeight="1">'."\n";
        $xml .= '<c r="A2" t="inlineStr" s="2"><is><t>Diekspor pada: '.$this->escapeXml($exportTime).' | Total Pelamar: '.$applications->count().' Orang</t></is></c>'."\n";
        $xml .= '</row>'."\n";

        // Row 3: Blank Row
        $xml .= '<row r="3" ht="10" customHeight="1"/>'."\n";

        // Row 4: Header Table
        $xml .= '<row r="4" ht="26" customHeight="1">'."\n";
        foreach ($headers as $colIdx => $headerText) {
            $cellRef = $this->getColumnLetter($colIdx).'4';
            $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="3"><is><t>'.$this->escapeXml($headerText).'</t></is></c>'."\n";
        }
        $xml .= '</row>'."\n";

        // Row 5+: Data Rows
        $rowIndex = 5;
        foreach ($applications as $appIndex => $app) {
            $user = $app->user;
            $profile = $user?->applicantProfile;
            $edu = $profile?->educations?->first();

            $genderRaw = strtolower(trim((string) ($profile?->gender ?? '')));
            $genderLabel = match (true) {
                in_array($genderRaw, ['laki-laki', 'male', 'l', 'pria'], true) => 'Laki-laki',
                in_array($genderRaw, ['perempuan', 'female', 'p', 'wanita'], true) => 'Perempuan',
                ! empty($profile?->gender) => (string) $profile->gender,
                default => '-',
            };

            $birthDateFormatted = $profile?->birth_date ? Carbon::parse($profile->birth_date)->format('d/m/Y') : '-';
            $age = $profile?->birth_date ? Carbon::parse($profile->birth_date)->age.' Thn' : '-';
            $submittedAtFormatted = $app->submitted_at ? Carbon::parse($app->submitted_at)->locale('id')->translatedFormat('d M Y, H:i') : '-';
            $statusLabel = $app->status instanceof JobApplicationStatus ? $app->status->label() : (string) ($app->status ?? '-');
            $accountStatus = ($user && $user->is_active) ? 'Aktif' : 'Nonaktif';
            $domicileStatus = ($profile && $profile->same_as_ktp) ? 'Sesuai KTP' : 'Domisili Berbeda';
            $addressDomicile = ($profile && $profile->same_as_ktp) ? ($profile->address_ktp ?? '-') : ($profile->address_domicile ?? '-');

            $rowData = [
                $appIndex + 1,
                $user?->registration_number ?? '-',
                $app->application_number ?? '-',
                $user?->name ?? '-',
                $profile?->nik ? "'".$profile->nik : '-',
                $genderLabel,
                $profile?->birth_place ?? '-',
                $birthDateFormatted,
                $age,
                $user?->email ?? '-',
                $user?->phone ?? '-',
                $profile?->address_ktp ?? '-',
                $addressDomicile,
                $domicileStatus,
                $app->jobVacancy?->title ?? '-',
                $app->jobVacancy?->unit ?? $app->jobVacancy?->department ?? '-',
                $edu?->education_level ?? $edu?->degree_level ?? '-',
                $edu?->institution_name ?? '-',
                $edu?->study_program ?? $edu?->major ?? '-',
                $edu?->graduation_year ?? '-',
                $edu?->gpa ?? '-',
                $edu?->accreditation ?? '-',
                $submittedAtFormatted,
                $statusLabel,
                $accountStatus,
            ];

            $rowStyle = ($appIndex % 2 === 1) ? 5 : 4;
            $xml .= '<row r="'.$rowIndex.'" ht="20" customHeight="1">'."\n";
            foreach ($rowData as $colIdx => $val) {
                $cellRef = $this->getColumnLetter($colIdx).$rowIndex;
                $cellStyle = in_array($colIdx, [3, 11, 12, 14, 15, 17, 18], true) ? 0 : $rowStyle;
                $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="'.$cellStyle.'"><is><t>'.$this->sanitizeCellValue($val).'</t></is></c>'."\n";
            }
            $xml .= '</row>'."\n";

            $rowIndex++;
        }

        $xml .= '</sheetData>'."\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Convert 0-indexed column integer to Excel Column letters (0 => A, 25 => Z, 26 => AA).
     */
    protected function getColumnLetter(int $index): string
    {
        $letter = '';
        $index = $index + 1;

        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $index = (int) (($index - $mod) / 26);
        }

        return $letter;
    }

    /**
     * Sanitize cell value to prevent formula injection in spreadsheets.
     */
    protected function sanitizeCellValue(mixed $value): string
    {
        $str = (string) $value;
        if (in_array(substr($str, 0, 1), ['=', '+', '-', '@', "\t", "\r"], true)) {
            $str = "'".$str;
        }

        return $this->escapeXml($str);
    }

    /**
     * Escape characters for valid XML output.
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
