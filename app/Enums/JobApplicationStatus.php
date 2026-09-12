<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case IN_PROCESS = 'in_process';
    case PASSED_ADMINISTRATION = 'passed_administration';
    case IN_SELECTION = 'in_selection';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Menunggu Verifikasi',
            self::IN_PROCESS => 'Sedang Diproses',
            self::PASSED_ADMINISTRATION => 'Lolos Administrasi',
            self::IN_SELECTION => 'Tahap Seleksi',
            self::ACCEPTED => 'Diterima',
            self::REJECTED => 'Tidak Lolos',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-100 text-slate-700 border-slate-200',
            self::SUBMITTED => 'bg-amber-50 text-amber-700 border-amber-200',
            self::IN_PROCESS, self::IN_SELECTION => 'bg-sky-50 text-[#0179B5] border-sky-200',
            self::PASSED_ADMINISTRATION => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::ACCEPTED => 'bg-teal-50 text-teal-800 border-teal-300 font-bold',
            self::REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
