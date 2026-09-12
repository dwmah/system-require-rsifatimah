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
     * 3. accepted_at mencatat waktu kelulusan seleksi akhir, sedangkan hired_at mencatat
     *    waktu resmi mulai bekerja (orientasi dan kontrak selesai) sebelum akun dinonaktifkan.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'recruitment_period_id',
        'job_vacancy_id',
        'application_number',
        'status',
        'submitted_at',
        'locked_at',
        'accepted_at',
        'rejected_at',
        'hired_at',
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
        return $this->hasMany(ApplicationStageHistory::class);
    }

    /**
     * Snapshot data beku lamaran pekerjaan saat diajukan resmi.
     */
    public function snapshot(): HasOne
    {
        return $this->hasOne(ApplicationSnapshot::class);
    }
}
