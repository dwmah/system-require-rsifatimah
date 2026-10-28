<?php

namespace App\Enums;

enum RecruitmentGuideType: string
{
    case FLOW = 'flow';
    case REQUIREMENTS = 'requirements';

    public function label(): string
    {
        return match ($this) {
            self::FLOW => 'Alur Pendaftaran',
            self::REQUIREMENTS => 'Persyaratan Umum',
        };
    }
}
