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
        Schema::table('application_stage_histories', function (Blueprint $table) {
            $table->dateTime('published_at')->nullable()->after('decided_at');
            $table->text('applicant_message')->nullable()->after('published_at');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dateTime('final_result_published_at')->nullable()->after('rejected_at');
            $table->text('final_result_message')->nullable()->after('final_result_published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('application_stage_histories', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'applicant_message']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['final_result_published_at', 'final_result_message']);
        });
    }
};
