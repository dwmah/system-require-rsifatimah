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
        Schema::create('application_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->unique()->constrained('job_applications')->cascadeOnDelete();
            $table->json('recruitment_period_data');
            $table->json('personal_data');
            $table->json('educations');
            $table->json('licenses');
            $table->json('certifications');
            $table->json('work_experiences');
            $table->json('vacancy_data');
            $table->timestamp('captured_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_snapshots');
    }
};
