<?php

namespace Database\Seeders;

use App\Enums\RecruitmentPeriodStatus;
use App\Models\RecruitmentPeriod;
use Illuminate\Database\Seeder;

class RecruitmentPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Periode rekrutmen aktif untuk pengembangan (development seed data)
        RecruitmentPeriod::updateOrCreate(
            ['code' => 'RSIF-2026'],
            [
                'name' => 'Rekrutmen SDM RSI Fatimah 2026',
                'registration_start_at' => now()->subDays(5),
                'registration_end_at' => now()->addDays(25),
                'status' => RecruitmentPeriodStatus::OPEN,
                'completed_at' => null,
            ]
        );
    }
}
