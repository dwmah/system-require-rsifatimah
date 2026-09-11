<?php

namespace App\Enums;

enum ApplicationStageStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case PASSED = 'passed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::IN_PROGRESS => 'Sedang Berjalan',
            self::PASSED => 'Lolos',
            self::FAILED => 'Tidak Lolos',
        };
    }
}
