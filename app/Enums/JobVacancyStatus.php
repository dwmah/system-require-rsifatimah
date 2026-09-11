<?php

namespace App\Enums;

enum JobVacancyStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::OPEN => 'Dibuka',
            self::CLOSED => 'Ditutup',
        };
    }
}
