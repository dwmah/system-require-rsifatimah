<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStageHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'application_stage_histories';

    /**
     * The attributes that are mass assignable.
     *
     * Invariant:
     * vacancy_selection_stage_id HARUS merupakan tahapan seleksi milik JobVacancy
     * yang dilamar oleh job_application terkait.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job_application_id',
        'vacancy_selection_stage_id',
        'status',
        'started_at',
        'decided_at',
        'note',
        'published_at',
        'applicant_message',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStageStatus::class,
            'started_at' => 'datetime',
            'decided_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Lamaran pekerjaan yang dievaluasi.
     */
    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    /**
     * Definisi tahapan seleksi terkait.
     */
    public function selectionStage(): BelongsTo
    {
        return $this->belongsTo(VacancySelectionStage::class, 'vacancy_selection_stage_id');
    }

    /**
     * Cek apakah riwayat tahapan memiliki file lampiran hasil.
     */
    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }

    /**
     * URL aman untuk mengunduh lampiran hasil tes tahapan.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
            return route('admin.applications.stages.download-attachment', [
                'application' => $this->job_application_id,
                'history' => $this->id,
            ]);
        }

        return route('portal.applications.stages.download-attachment', [
            'jobApplication' => $this->job_application_id,
            'history' => $this->id,
        ]);
    }
}
