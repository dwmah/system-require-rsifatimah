<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case IN_PROCESS = 'in_process';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Terkirim',
            self::IN_PROCESS => 'Dalam Proses',
            self::ACCEPTED => 'Diterima',
            self::REJECTED => 'Tidak Lolos',
        };
    }
}
