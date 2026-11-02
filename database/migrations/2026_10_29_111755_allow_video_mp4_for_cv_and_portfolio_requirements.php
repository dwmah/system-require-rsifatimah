<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('vacancy_document_requirements')
            ->where(function ($query) {
                $query->where('name', 'like', '%CV%')
                    ->orWhere('name', 'like', '%Curriculum%')
                    ->orWhere('name', 'like', '%Portofolio%')
                    ->orWhere('name', 'like', '%Portfolio%');
            })
            ->update([
                'allowed_mimes' => 'application/pdf,video/mp4',
                'max_size_kb' => DB::raw('CASE WHEN max_size_kb < 20480 THEN 20480 ELSE max_size_kb END'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op safe rollback
    }
};
