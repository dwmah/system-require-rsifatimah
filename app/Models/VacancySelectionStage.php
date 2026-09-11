<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacancySelectionStage extends Model
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
        'sort_order',
        'start_at',
        'end_at',
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
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    /**
     * Lowongan pekerjaan pemilik tahapan seleksi.
     */
    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    /**
     * Riwayat evaluasi kandidat pada tahapan ini.
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(ApplicationStageHistory::class);
    }
}
