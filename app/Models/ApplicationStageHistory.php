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
}
