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
        Schema::create('application_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            
            // Invariant: vacancy_selection_stage_id harus milik job_vacancy yang dilamar oleh job_application
            $table->foreignId('vacancy_selection_stage_id')->constrained('vacancy_selection_stages')->restrictOnDelete();
            
            $table->string('status')->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            // Satu tahapan seleksi hanya dicatat sekali per lamaran
            $table->unique(['job_application_id', 'vacancy_selection_stage_id'], 'app_stage_hist_app_stage_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_stage_histories');
    }
};
