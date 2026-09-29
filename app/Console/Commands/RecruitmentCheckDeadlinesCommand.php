<?php

namespace App\Console\Commands;

use App\Enums\RecruitmentPeriodStatus;
use App\Models\RecruitmentPeriod;
use Illuminate\Console\Command;

class RecruitmentCheckDeadlinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa dan kunci periode seleksi yang telah melewati batas akhir waktu submit dokumen (Date-Driven Lock)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa status batas waktu pendaftaran rekrutmen...');

        $now = now();
        $expiredPeriods = RecruitmentPeriod::where('status', RecruitmentPeriodStatus::OPEN)
            ->where('registration_end_at', '<', $now)
            ->get();

        foreach ($expiredPeriods as $period) {
            $this->warn("Periode '{$period->name}' telah melewati batas submission ({$period->registration_end_at}). Form upload dikunci otomatis.");
        }

        $this->info('Pemeriksaan batas waktu selesai.');

        return Command::SUCCESS;
    }
}
