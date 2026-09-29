<?php

namespace App\Services\Recruitment;

use App\Enums\JobApplicationStatus;
use App\Exceptions\Recruitment\DocumentIntegrityException;
use App\Exceptions\Recruitment\MissingRequiredDocumentsException;
use App\Models\ApplicationDocument;
use App\Models\JobApplication;
use App\Models\VacancyDocumentRequirement;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ApplicationDocumentService
{
    public const DISK_NAME = 'applicant_documents';

    public function __construct(
        protected ?JobApplicationService $jobApplicationService = null
    ) {
        $this->jobApplicationService = $jobApplicationService ?? app(JobApplicationService::class);
    }

    /**
     * Mapping MIME canonical ke ekstensi aman yang didukung sistem.
     */
    public static function mimeToExtension(string $mime): ?string
    {
        return match (strtolower(trim($mime))) {
            'application/pdf' => 'pdf',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            default => null,
        };
    }

    /**
     * Menormalisasi token format menjadi canonical MIME type standar.
     * Mendukung token format ekstensi ('pdf', 'jpg', 'png') maupun MIME ('application/pdf').
     */
    public static function canonicalizeMimeToken(string $token): ?string
    {
        $normalized = strtolower(trim($token));

        return match ($normalized) {
            'pdf', 'application/pdf' => 'application/pdf',
            'jpg', 'jpeg', 'image/jpeg', 'image/jpg' => 'image/jpeg',
            'png', 'image/png' => 'image/png',
            default => null,
        };
    }

    /**
     * Mem-parsing konfigurasi allowed_mimes requirement secara aman dan fail-closed.
     *
     * @return list<string>
     *
     * @throws DomainException
     */
    public function parseAllowedMimes(?string $allowedMimesConfig): array
    {
        if ($allowedMimesConfig === null || trim($allowedMimesConfig) === '') {
            throw new DomainException('Konfigurasi tipe file (allowed_mimes) pada persyaratan dokumen tidak valid atau kosong.');
        }

        $tokens = array_filter(array_unique(array_map('trim', explode(',', $allowedMimesConfig))));
        if (empty($tokens)) {
            throw new DomainException('Konfigurasi tipe file (allowed_mimes) tidak memuat tipe berkas yang valid.');
        }

        $canonicalMimes = [];
        foreach ($tokens as $token) {
            $canonical = self::canonicalizeMimeToken($token);
            if ($canonical === null) {
                // FAIL CLOSED on unknown token
                throw new DomainException("Tipe berkas '{$token}' tidak didukung oleh sistem rekrutmen.");
            }
            $canonicalMimes[] = $canonical;
        }

        return array_values(array_unique($canonicalMimes));
    }

    /**
     * Memvalidasi konfigurasi ukuran maksimal file (max_size_kb) secara fail-closed.
     *
     * @throws DomainException
     */
    public function validateMaxSizeBytes(?int $maxSizeKb): int
    {
        if ($maxSizeKb === null || $maxSizeKb <= 0) {
            throw new DomainException('Konfigurasi batas ukuran file (max_size_kb) tidak valid atau kosong.');
        }

        return $maxSizeKb * 1024;
    }

    /**
     * Validasi berkas upload secara menyeluruh di sisi server.
     *
     * @throws ValidationException|DomainException
     */
    public function validateFile(VacancyDocumentRequirement $requirement, UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['File yang diunggah mengalami kerusakan atau gagal terkirim.'],
            ]);
        }

        $maxBytes = $this->validateMaxSizeBytes($requirement->max_size_kb);
        if ($file->getSize() > $maxBytes) {
            $maxMb = round($requirement->max_size_kb / 1024, 1);
            throw ValidationException::withMessages([
                'file' => ["Ukuran file melebihi batas maksimal yang diizinkan ({$maxMb} MB)."],
            ]);
        }

        $allowedMimes = $this->parseAllowedMimes($requirement->allowed_mimes);
        $detectedMime = $file->getMimeType() ?: $file->getClientMimeType();

        if (! in_array($detectedMime, $allowedMimes, true)) {
            $readableFormats = implode(', ', array_map(fn ($m) => strtoupper(self::mimeToExtension($m) ?? $m), $allowedMimes));
            throw ValidationException::withMessages([
                'file' => ["Format file ({$detectedMime}) tidak diizinkan. Format yang diperbolehkan: {$readableFormats}."],
            ]);
        }
    }

    /**
     * Membersihkan nama file original untuk keamanan header Content-Disposition.
     */
    public function sanitizeOriginalFilename(string $filename): string
    {
        $cleaned = preg_replace('/[\r\n\t"\']/', '', $filename);
        $cleaned = basename($cleaned);

        return $cleaned !== '' ? $cleaned : 'dokumen.pdf';
    }

    /**
     * Mengunggah atau mengganti berkas persyaratan lamaran secara aman.
     *
     * Alur Penggantian Aman:
     * 1. Validasi berkas baru.
     * 2. Simpan berkas baru ke storage privat.
     * 3. Update/create metadata database.
     * 4. Jika DB gagal: hapus berkas BARU (berkas lama tetap aman).
     * 5. Jika DB sukses: hapus berkas LAMA secara aman.
     *
     * @throws ValidationException|DomainException|RuntimeException
     */
    public function uploadOrReplace(
        JobApplication $application,
        VacancyDocumentRequirement $requirement,
        UploadedFile $file
    ): ApplicationDocument {
        // 1. Verifikasi Invariant Kepemilikan Requirement
        $this->jobApplicationService->validateDocumentRequirement($application, $requirement);

        // 2. Verifikasi Status Draft & Unlocked
        if ($application->status !== JobApplicationStatus::DRAFT || $application->locked_at !== null) {
            throw new DomainException('Tidak dapat mengunggah berkas pada lamaran yang telah dikirim atau dikunci.');
        }

        // 3. Validasi Berkas Server-side
        $this->validateFile($requirement, $file);

        $detectedMime = $file->getMimeType() ?: $file->getClientMimeType();
        $extension = self::mimeToExtension($detectedMime) ?: 'pdf';
        $safeOriginalName = $this->sanitizeOriginalFilename($file->getClientOriginalName());

        // 4. Struktur Path Server-Generated dengan UUID
        $uuid = (string) Str::uuid();
        $relativeDirectory = "{$application->id}/requirements/{$requirement->id}";
        $filename = "{$uuid}.{$extension}";
        $newRelativePath = "{$relativeDirectory}/{$filename}";

        $disk = Storage::disk(self::DISK_NAME);

        // 5. Simpan file baru ke private storage
        $stored = $disk->putFileAs($relativeDirectory, $file, $filename);
        if (! $stored || ! $disk->exists($newRelativePath)) {
            throw new RuntimeException('Gagal menyimpan file ke private storage.');
        }

        // Cari dokumen lama jika ada
        $existingDocument = ApplicationDocument::where('job_application_id', $application->id)
            ->where('vacancy_document_requirement_id', $requirement->id)
            ->first();

        $oldPath = $existingDocument?->stored_path;

        // 6. Mutasi Database Atomik
        try {
            if ($existingDocument) {
                $existingDocument->update([
                    'original_name' => $safeOriginalName,
                    'stored_path' => $newRelativePath,
                    'mime_type' => $detectedMime,
                    'file_size' => $file->getSize(),
                    'verification_status' => 'pending',
                    'verification_note' => null,
                ]);
                $document = $existingDocument->fresh();
            } else {
                $document = ApplicationDocument::create([
                    'job_application_id' => $application->id,
                    'vacancy_document_requirement_id' => $requirement->id,
                    'original_name' => $safeOriginalName,
                    'stored_path' => $newRelativePath,
                    'mime_type' => $detectedMime,
                    'file_size' => $file->getSize(),
                    'verification_status' => 'pending',
                    'verification_note' => null,
                ]);
            }
        } catch (Throwable $dbException) {
            // Rollback physical file: Hapus file BARU agar tidak meninggalkan file yatim
            try {
                $disk->delete($newRelativePath);
            } catch (Throwable) {
                // Abaikan exception rollback storage
            }

            throw $dbException;
        }

        // 7. Bersihkan file LAMA setelah database terbukti sukses di-update
        if ($oldPath && $oldPath !== $newRelativePath) {
            try {
                if ($disk->exists($oldPath)) {
                    $disk->delete($oldPath);
                }
            } catch (Throwable) {
                Log::warning('Gagal menghapus file lama paska penggantian berkas', [
                    'application_id' => $application->id,
                    'requirement_id' => $requirement->id,
                ]);
            }
        }

        return $document;
    }

    /**
     * Menghapus dokumen berkas persyaratan pelamar.
     *
     * @throws DomainException
     */
    public function delete(JobApplication $application, ApplicationDocument $document): void
    {
        if ($document->job_application_id !== $application->id) {
            throw new DomainException('Dokumen tidak sesuai dengan lamaran terkait.');
        }

        if ($application->status !== JobApplicationStatus::DRAFT || $application->locked_at !== null) {
            throw new DomainException('Tidak dapat menghapus berkas pada lamaran yang telah dikirim atau dikunci.');
        }

        $path = $document->stored_path;

        // Hapus metadata database
        $document->delete();

        // Hapus file fisik privat
        $this->deletePhysicalFile($path);
    }

    /**
     * Menghapus file fisik dari private storage disk.
     */
    public function deletePhysicalFile(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $disk = Storage::disk(self::DISK_NAME);
        if ($disk->exists($path)) {
            return $disk->delete($path);
        }

        return false;
    }

    /**
     * Mengembalikan daftar requirement dokumen wajib yang belum diunggah.
     *
     * @return Collection<int, VacancyDocumentRequirement>
     */
    public function missingRequiredDocuments(JobApplication $application): Collection
    {
        $application->loadMissing(['jobVacancy.documentRequirements', 'documents']);

        $uploadedRequirementIds = $application->documents
            ->pluck('vacancy_document_requirement_id')
            ->all();

        return $application->jobVacancy->documentRequirements
            ->where('is_required', true)
            ->filter(fn ($req) => ! in_array($req->id, $uploadedRequirementIds, true))
            ->values();
    }

    /**
     * Memeriksa apakah seluruh dokumen persyaratan wajib telah diunggah.
     */
    public function hasAllRequiredDocuments(JobApplication $application): bool
    {
        return $this->missingRequiredDocuments($application)->isEmpty();
    }

    /**
     * Memvalidasi integritas seluruh dokumen lamaran sebelum Final Submit.
     * Menjamin bahwa file fisik benar-benar ada di private disk dan memenuhi syarat terkini.
     *
     * @throws MissingRequiredDocumentsException|DocumentIntegrityException
     */
    public function validateDocumentsForSubmission(JobApplication $application): void
    {
        $application->loadMissing(['jobVacancy.documentRequirements', 'documents']);

        $missingRequired = $this->missingRequiredDocuments($application);
        if ($missingRequired->isNotEmpty()) {
            throw new MissingRequiredDocumentsException($missingRequired->pluck('name'));
        }

        $disk = Storage::disk(self::DISK_NAME);
        $requirementsMap = $application->jobVacancy->documentRequirements->keyBy('id');

        foreach ($application->documents as $document) {
            // 1. Requirement harus valid untuk lowongan ini
            $req = $requirementsMap->get($document->vacancy_document_requirement_id);
            if (! $req) {
                throw new DocumentIntegrityException("Persyaratan dokumen #{$document->vacancy_document_requirement_id} tidak valid untuk lowongan ini.");
            }

            // 2. File fisik harus ada pada private storage disk
            if (! $document->stored_path || ! $disk->exists($document->stored_path)) {
                throw new DocumentIntegrityException("File fisik untuk dokumen '{$req->name}' tidak ditemukan pada storage server.");
            }

            // 3. MIME type harus tetap diizinkan oleh CURRENT requirement
            $allowedMimes = $this->parseAllowedMimes($req->allowed_mimes);
            if (! in_array($document->mime_type, $allowedMimes, true)) {
                throw new DocumentIntegrityException("Format berkas '{$req->name}' ({$document->mime_type}) sudah tidak lagi diizinkan oleh ketentuan terbaru lowongan.");
            }

            // 4. File size harus <= CURRENT max_size_kb
            $maxBytes = $this->validateMaxSizeBytes($req->max_size_kb);
            if ($document->file_size > $maxBytes) {
                throw new DocumentIntegrityException("Ukuran berkas '{$req->name}' melebihi ketentuan ukuran terbaru lowongan.");
            }
        }
    }
}
