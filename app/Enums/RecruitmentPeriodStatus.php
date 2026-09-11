<?php

namespace App\Enums;

enum RecruitmentPeriodStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case SELECTION = 'selection';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::OPEN => 'Pendaftaran Dibuka',
            self::SELECTION => 'Proses Seleksi',
            self::COMPLETED => 'Selesai',
        };
    }
}
