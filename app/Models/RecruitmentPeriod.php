<?php

namespace App\Models;

use App\Enums\RecruitmentPeriodStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruitmentPeriod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'registration_start_at',
        'registration_end_at',
        'status',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_start_at' => 'datetime',
            'registration_end_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => RecruitmentPeriodStatus::class,
        ];
    }

    /**
     * Scope query untuk periode rekrutmen yang saat ini sedang dibuka untuk pendaftaran publik.
     */
    public function scopeCurrentlyOpen(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $time = $at ?? now();

        return $query->where('status', RecruitmentPeriodStatus::OPEN)
            ->where('registration_start_at', '<=', $time)
            ->where('registration_end_at', '>=', $time);
    }

    /**
     * Memeriksa apakah instance periode rekrutmen ini saat ini sedang aktif dibuka.
     */
    public function isCurrentlyOpen(?CarbonInterface $at = null): bool
    {
        $time = $at ?? now();

        return $this->status === RecruitmentPeriodStatus::OPEN
            && $this->registration_start_at !== null
            && $this->registration_end_at !== null
            && $this->registration_start_at->lte($time)
            && $this->registration_end_at->gte($time);
    }

    public function isOpenForSubmission(): bool
    {
        return $this->isCurrentlyOpen();
    }

    /**
     * Alias for submission deadline
     */
    public function getSubmissionDeadlineAttribute()
    {
        return $this->registration_end_at;
    }

    public function getStartDateAttribute()
    {
        return $this->registration_start_at;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === RecruitmentPeriodStatus::OPEN;
    }

    /**
     * Daftar lowongan pekerjaan yang dibuka pada periode ini.
     */
    public function jobVacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class);
    }

    public function vacancies(): HasMany
    {
        return $this->jobVacancies();
    }

    /**
     * Seluruh lamaran yang masuk pada periode rekrutmen ini.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
