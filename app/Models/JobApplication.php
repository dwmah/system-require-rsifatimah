<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * Invariants:
     * 1. recruitment_period_id HARUS sama dengan job_vacancy.recruitment_period_id.
     * 2. Satu user hanya memiliki satu aplikasi aktif dalam satu periode rekrutmen.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'recruitment_period_id',
        'job_vacancy_id',
        'application_number',
        'status',
        'current_stage_id',
        'submitted_at',
        'locked_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'announcement_seen_at',
        'verified_at',
        'verified_by',
        'final_result_published_at',
        'final_result_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => JobApplicationStatus::class,
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'hired_at' => 'datetime',
            'announcement_seen_at' => 'datetime',
            'verified_at' => 'datetime',
            'final_result_published_at' => 'datetime',
        ];
    }

    /**
     * Akun pelamar yang mengajukan lamaran.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Periode rekrutmen tempat lamaran diajukan.
     */
    public function recruitmentPeriod(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPeriod::class);
    }

    /**
     * Lowongan pekerjaan yang dilamar.
     */
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    /**
     * Tahapan seleksi aktif saat ini.
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(VacancySelectionStage::class, 'current_stage_id');
    }

    /**
     * Admin/Verifikator yang melakukan verifikasi berkas.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Seluruh dokumen/berkas yang diunggah untuk lamaran ini.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * Riwayat evaluasi tahapan seleksi untuk lamaran ini.
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(ApplicationStageHistory::class)->orderBy('id', 'asc');
    }

    /**
     * Helper status checks
     */
    public function isDraft(): bool
    {
        return $this->status === JobApplicationStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === JobApplicationStatus::SUBMITTED;
    }

    public function isPassedAdministration(): bool
    {
        return in_array($this->status, [
            JobApplicationStatus::PASSED_ADMINISTRATION,
            JobApplicationStatus::IN_SELECTION,
            JobApplicationStatus::ACCEPTED,
        ]);
    }

    public function isRejected(): bool
    {
        return $this->status === JobApplicationStatus::REJECTED;
    }

    public function isAccepted(): bool
    {
        return $this->status === JobApplicationStatus::ACCEPTED;
    }

    public function isLocked(): bool
    {
        return ! is_null($this->locked_at) || ! $this->isDraft();
    }

    /**
     * Snapshot data beku lamaran pekerjaan saat diajukan resmi.
     */
    public function snapshot(): HasOne
    {
        return $this->hasOne(ApplicationSnapshot::class);
    }
}
