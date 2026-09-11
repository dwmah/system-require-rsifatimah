<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancyDocumentRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job_vacancy_id',
        'name',
        'description',
        'is_required',
        'allowed_mimes',
        'max_size_kb',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'max_size_kb' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Lowongan pekerjaan pemilik syarat dokumen.
     */
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    /**
     * Berkas-berkas lamaran yang diunggah untuk persyaratan ini.
     */
    public function applicationDocuments(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }
}
