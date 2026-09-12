<?php

namespace App\Services\Recruitment;

use App\Exceptions\Recruitment\ApplicationAlreadySnapshottedException;
use App\Models\ApplicationSnapshot;
use App\Models\JobApplication;
use Carbon\CarbonInterface;

class ApplicationSnapshotService
{
    /**
     * Menangkap snapshot beku (immutable state) dari seluruh data pelamar,
     * lowongan, dan periode rekrutmen pada saat lamaran diajukan resmi.
     *
     * Invariants:
     * - 1 JobApplication hanya memiliki maksimal 1 ApplicationSnapshot.
     * - Format tanggal terstandarisasi deterministik: YYYY-MM-DD (date) dan ISO 8601 (datetime).
     * - Tidak menyertakan atribut autentikasi/sensitif (password, token, session).
     *
     * @throws ApplicationAlreadySnapshottedException
     */
    public function capture(JobApplication $application, ?CarbonInterface $capturedAt = null): ApplicationSnapshot
    {
        if ($application->snapshot()->exists()) {
            throw new ApplicationAlreadySnapshottedException();
        }

        $user = $application->user;
        $profile = $user->applicantProfile;
        $vacancy = $application->jobVacancy;
        $period = $application->recruitmentPeriod;

        // 1. Data Periode Rekrutmen
        $periodData = [
            'id' => $period->id,
            'code' => $period->code,
            'name' => $period->name,
            'registration_start_at' => $period->registration_start_at?->toIso8601String(),
            'registration_end_at' => $period->registration_end_at?->toIso8601String(),
        ];

        // 2. Data Pribadi Pelamar (hanya atribut profil dasar)
        $personalData = [
            'registration_number' => $user->registration_number,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'nik' => $profile?->nik,
            'birth_place' => $profile?->birth_place,
            'birth_date' => $profile?->birth_date?->format('Y-m-d'),
            'gender' => $profile?->gender,
            'address_ktp' => $profile?->address_ktp,
            'address_domicile' => $profile?->address_domicile,
        ];

        // 3. Riwayat Pendidikan (Deterministik: Tahun Lulus desc, id asc)
        $educationsData = $profile?->educations()
            ->orderByDesc('graduation_year')
            ->orderBy('id')
            ->get()
            ->map(fn ($edu) => [
                'education_level' => $edu->education_level,
                'institution_name' => $edu->institution_name,
                'study_program' => $edu->study_program,
                'accreditation' => $edu->accreditation,
                'gpa' => $edu->gpa !== null ? number_format((float) $edu->gpa, 2, '.', '') : null,
                'graduation_year' => $edu->graduation_year,
            ])
            ->values()
            ->toArray() ?? [];

        // 4. STR / Lisensi Profesi (Deterministik: id asc)
        $licensesData = $profile?->licenses()
            ->orderBy('id')
            ->get()
            ->map(fn ($lic) => [
                'license_type' => $lic->license_type,
                'license_number' => $lic->license_number,
                'issued_at' => $lic->issued_at?->format('Y-m-d'),
                'expires_at' => $lic->expires_at?->format('Y-m-d'),
            ])
            ->values()
            ->toArray() ?? [];

        // 5. Sertifikasi Kompetensi / Pelatihan (Deterministik: id asc)
        $certificationsData = $profile?->certifications()
            ->orderBy('id')
            ->get()
            ->map(fn ($cert) => [
                'name' => $cert->name,
                'issuer' => $cert->issuer,
                'certificate_number' => $cert->certificate_number,
                'issued_at' => $cert->issued_at?->format('Y-m-d'),
                'expires_at' => $cert->expires_at?->format('Y-m-d'),
            ])
            ->values()
            ->toArray() ?? [];

        // 6. Pengalaman Kerja (Deterministik: start_date desc, id asc)
        $workExperiencesData = $profile?->workExperiences()
            ->orderByDesc('start_date')
            ->orderBy('id')
            ->get()
            ->map(fn ($exp) => [
                'company_name' => $exp->company_name,
                'position' => $exp->position,
                'start_date' => $exp->start_date?->format('Y-m-d'),
                'end_date' => $exp->end_date?->format('Y-m-d'),
                'description' => $exp->description,
            ])
            ->values()
            ->toArray() ?? [];

        // 7. Master Lowongan dan Persyaratannya (Deterministik dengan Stable Master IDs)
        $qualifications = $vacancy->qualifications()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($q) => [
                'id' => $q->id,
                'label' => $q->label,
                'value' => $q->value,
                'sort_order' => $q->sort_order,
            ])
            ->values()
            ->toArray();

        $documentRequirements = $vacancy->documentRequirements()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'description' => $d->description,
                'is_required' => (bool) $d->is_required,
                'allowed_mimes' => $d->allowed_mimes,
                'max_size_kb' => $d->max_size_kb,
                'sort_order' => $d->sort_order,
            ])
            ->values()
            ->toArray();

        $selectionStages = $vacancy->selectionStages()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'sort_order' => $s->sort_order,
                'start_at' => $s->start_at?->toIso8601String(),
                'end_at' => $s->end_at?->toIso8601String(),
            ])
            ->values()
            ->toArray();

        $vacancyData = [
            'id' => $vacancy->id,
            'code' => $vacancy->code,
            'title' => $vacancy->title,
            'unit' => $vacancy->unit,
            'employment_type' => $vacancy->employment_type,
            'quota' => $vacancy->quota,
            'location' => $vacancy->location,
            'description' => $vacancy->description,
            'responsibilities' => $vacancy->responsibilities,
            'open_at' => $vacancy->open_at?->toIso8601String(),
            'close_at' => $vacancy->close_at?->toIso8601String(),
            'qualifications' => $qualifications,
            'document_requirements' => $documentRequirements,
            'selection_stages' => $selectionStages,
        ];

        return ApplicationSnapshot::create([
            'job_application_id' => $application->id,
            'recruitment_period_data' => $periodData,
            'personal_data' => $personalData,
            'educations' => $educationsData,
            'licenses' => $licensesData,
            'certifications' => $certificationsData,
            'work_experiences' => $workExperiencesData,
            'vacancy_data' => $vacancyData,
            'captured_at' => $capturedAt ?? now(),
        ]);
    }
}
