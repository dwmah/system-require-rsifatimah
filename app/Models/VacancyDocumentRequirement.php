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

    public function getDocumentNameAttribute(): ?string
    {
        return $this->name;
    }

    public function setDocumentNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getIsMandatoryAttribute(): bool
    {
        return (bool) $this->is_required;
    }

    public function setIsMandatoryAttribute($value): void
    {
        $this->attributes['is_required'] = (bool) $value;
    }

    public function getSequenceOrderAttribute(): int
    {
        return $this->sort_order ?? 1;
    }

    public function setSequenceOrderAttribute($value): void
    {
        $this->attributes['sort_order'] = (int) $value;
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
