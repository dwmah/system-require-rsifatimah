<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacancyQualification extends Model
{
    use HasFactory;

    /**
     * Label kanonikal untuk kualifikasi jenjang pendidikan.
     */
    public const LABEL_EDUCATION = 'Pendidikan Formal';

    /**
     * Infer or resolve a semantic label from qualification description.
     */
    public static function resolveSemanticLabel(string $value, ?string $explicitLabel = null, int $index = 0): string
    {
        if (! empty($explicitLabel) && ! preg_match('/^kualifikasi\s*\d+$/i', trim($explicitLabel))) {
            return trim($explicitLabel);
        }

        $val = strtolower($value);
        if (preg_match('/(d3|d4|s1|s2|s3|profesi|ners|apoteker|dokter|bidan|keperawatan|teknik|ijazah|lulusan|pendidikan|smk|sma|diploma|sarjana)/i', $val)) {
            return 'Pendidikan';
        }
        if (preg_match('/(ipk|indeks prestasi|gpa)/i', $val)) {
            return 'IPK Minimum';
        }
        if (preg_match('/\b(str|stra|strp|sip|surat tanda registrasi)\b/i', $val)) {
            return 'STR';
        }
        if (preg_match('/(usia|umur|\d+\s*tahun|thn)/i', $val) && preg_match('/(maks|maksimal|maksimum|paling tinggi|rentang)/i', $val)) {
            return 'Usia Maksimal';
        }
        if (preg_match('/(pengalaman|berpengalaman|fresh graduate|jam terbang|pengalaman kerja)/i', $val)) {
            return 'Pengalaman';
        }
        if (preg_match('/(sertifikat|sertifikasi|btcls|ppgd|acls|atls|pelatihan|kursus)/i', $val)) {
            return 'Sertifikasi';
        }
        if (preg_match('/(menguasai|pemrograman|php|laravel|sql|mikrotik|ct-scan|x-ray|flebotomi|keahlian|keterampilan)/i', $val)) {
            return 'Keahlian Khusus';
        }

        return 'Kualifikasi Khusus';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job_vacancy_id',
        'label',
        'value',
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
            'sort_order' => 'integer',
        ];
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->value ?? $this->label;
    }

    public function setDescriptionAttribute(?string $val): void
    {
        $this->attributes['value'] = $val;
        if (empty($this->attributes['label'])) {
            $this->attributes['label'] = 'Kualifikasi Umum';
        }
    }

    /**
     * Lowongan yang memiliki kualifikasi ini.
     */
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }
}
