<?php

namespace App\Console\Commands;

use App\Services\Lifecycle\UserLifecycleService;
use Illuminate\Console\Command;

class RecruitmentCleanupRejectedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recruitment:cleanup-rejected';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan berkas dan data pelamar yang tidak lolos seleksi setelah membuka pengumuman';

    /**
     * Execute the console command.
     */
    public function handle(UserLifecycleService $lifecycleService): int
    {
        $this->info('Memulai pembersihan data & berkas pelamar tidak lolos...');

        $count = $lifecycleService->cleanupAllSeenRejectedApplicants();

        $this->info("Pembersihan selesai: {$count} data pelamar tidak lolos berhasil dibersihkan dari server.");

        return Command::SUCCESS;
    }
}
