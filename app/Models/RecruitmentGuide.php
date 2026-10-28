<?php

namespace App\Models;

use App\Enums\RecruitmentGuideType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentGuide extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recruitment_guides';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'title',
        'slug',
        'subtitle',
        'content',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => RecruitmentGuideType::class,
        'content' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active guides.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include flow guides.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeFlow(Builder $query): Builder
    {
        return $query->where('type', RecruitmentGuideType::FLOW->value);
    }

    /**
     * Scope a query to only include requirements guides.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeRequirements(Builder $query): Builder
    {
        return $query->where('type', RecruitmentGuideType::REQUIREMENTS->value);
    }
}
