<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Invariant:
     * vacancy_document_requirement_id HARUS merupakan persyaratan milik JobVacancy
     * yang dilamar oleh job_application terkait.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job_application_id',
        'vacancy_document_requirement_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'verification_status',
        'verification_note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Lamaran pekerjaan pemilik dokumen ini.
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    /**
     * Definisi persyaratan dokumen dari lowongan terkait.
     */
    public function documentRequirement(): BelongsTo
    {
        return $this->belongsTo(VacancyDocumentRequirement::class, 'vacancy_document_requirement_id');
    }
}
