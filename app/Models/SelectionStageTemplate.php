<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelectionStageTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_default',
        'stages_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'stages_data' => 'array',
        ];
    }

    /**
     * Mendapatkan template default aktif.
     */
    public static function getDefaultTemplate(): ?self
    {
        return static::where('is_default', true)->first() ?: static::first();
    }
}
