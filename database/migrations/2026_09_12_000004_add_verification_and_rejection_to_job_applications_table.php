<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('current_stage_id')->nullable()->after('status')->constrained('vacancy_selection_stages')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->timestamp('announcement_seen_at')->nullable()->after('rejection_reason');
            $table->timestamp('verified_at')->nullable()->after('announcement_seen_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['current_stage_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'current_stage_id',
                'rejection_reason',
                'announcement_seen_at',
                'verified_at',
                'verified_by',
            ]);
        });
    }
};
