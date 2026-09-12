<?php

namespace App\Models;

use App\Enums\JobVacancyStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobVacancy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'recruitment_period_id',
        'code',
        'title',
        'unit',
        'employment_type',
        'quota',
        'location',
        'description',
        'responsibilities',
        'open_at',
        'close_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'open_at' => 'datetime',
            'close_at' => 'datetime',
            'status' => JobVacancyStatus::class,
        ];
    }

    /**
     * Aliases for seamless schema compatibility
     */
    public function getDepartmentAttribute(): ?string
    {
        return $this->unit;
    }

    public function setDepartmentAttribute(?string $value): void
    {
        $this->attributes['unit'] = $value ?? 'Pelayanan Medis';
    }

    public function getFormationTypeAttribute(): ?string
    {
        return $this->employment_type ?? 'Tenaga Keperawatan';
    }

    public function setFormationTypeAttribute(?string $value): void
    {
        $this->attributes['employment_type'] = $value ?? 'Tenaga Keperawatan';
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === JobVacancyStatus::OPEN;
    }

    public function setIsPublishedAttribute($value): void
    {
        $this->attributes['status'] = $value ? JobVacancyStatus::OPEN->value : JobVacancyStatus::DRAFT->value;
        if ($value && is_null($this->open_at)) {
            $this->attributes['open_at'] = now();
            $this->attributes['close_at'] = now()->addMonth();
        }
    }

    public function getSummaryAttribute(): ?string
    {
        return $this->description ? \Illuminate\Support\Str::limit(strip_tags($this->description), 150) : null;
    }

    /**
     * Scope query untuk lowongan yang benar-benar aktif dibuka:
     * 1. Status lowongan OPEN
     * 2. Berada dalam rentang tanggal open_at s/d close_at
     * 3. Periode rekrutmen induknya juga sedang aktif dibuka (currentlyOpen)
     */
    public function scopeCurrentlyOpen(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $time = $at ?? now();

        return $query->where('status', JobVacancyStatus::OPEN)
            ->where(function ($q) use ($time) {
                $q->whereNull('open_at')->orWhere('open_at', '<=', $time);
            })
            ->where(function ($q) use ($time) {
                $q->whereNull('close_at')->orWhere('close_at', '>=', $time);
            })
            ->whereHas('recruitmentPeriod', function (Builder $periodQuery) use ($time) {
                $periodQuery->currentlyOpen($time);
            });
    }

    /**
     * Memeriksa apakah lowongan ini saat ini sedang aktif dibuka beserta periodenya.
     */
    public function isCurrentlyOpen(?CarbonInterface $at = null): bool
    {
        $time = $at ?? now();

        $isVacancyActive = $this->status === JobVacancyStatus::OPEN
            && ($this->open_at === null || $this->open_at->lte($time))
            && ($this->close_at === null || $this->close_at->gte($time));

        if (! $isVacancyActive) {
            return false;
        }

        $period = $this->relationLoaded('recruitmentPeriod')
            ? $this->recruitmentPeriod
            : $this->recruitmentPeriod()->first();

        return $period ? $period->isCurrentlyOpen($time) : false;
    }

    /**
     * Helper bisnis untuk memeriksa apakah saat ini ada rekrutmen/lowongan yang sedang dibuka untuk publik.
     */
    public static function hasActiveRecruitment(?CarbonInterface $at = null): bool
    {
        return static::currentlyOpen($at)->exists();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('code', $value)->orWhere('id', $value)->firstOrFail();
    }

    /**
     * Periode rekrutmen tempat lowongan ini bernaung.
     */
    public function recruitmentPeriod(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPeriod::class);
    }

    /**
     * Kualifikasi dinamis yang dibutuhkan lowongan ini.
     */
    public function qualifications(): HasMany
    {
        return $this->hasMany(VacancyQualification::class)->orderBy('sort_order');
    }

    /**
     * Persyaratan berkas/dokumen untuk lowongan ini.
     */
    public function documentRequirements(): HasMany
    {
        return $this->hasMany(VacancyDocumentRequirement::class)->orderBy('sort_order');
    }

    /**
     * Tahapan seleksi dinamis untuk lowongan ini.
     */
    public function selectionStages(): HasMany
    {
        return $this->hasMany(VacancySelectionStage::class)->orderBy('sort_order');
    }

    /**
     * Seluruh lamaran yang masuk untuk lowongan ini.
     */
    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
