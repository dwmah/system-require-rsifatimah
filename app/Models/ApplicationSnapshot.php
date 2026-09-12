<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job_application_id',
        'recruitment_period_data',
        'personal_data',
        'educations',
        'licenses',
        'certifications',
        'work_experiences',
        'vacancy_data',
        'captured_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recruitment_period_data' => 'array',
            'personal_data' => 'array',
            'educations' => 'array',
            'licenses' => 'array',
            'certifications' => 'array',
            'work_experiences' => 'array',
            'vacancy_data' => 'array',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * Lamaran pekerjaan pemilik snapshot data ini.
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
