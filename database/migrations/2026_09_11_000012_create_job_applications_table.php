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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Invariant: recruitment_period_id HARUS sama dengan job_vacancies.recruitment_period_id.
            // Dipertahankan sebagai foreign key normal dan di-enforce via validasi aplikasi/service layer
            // untuk menjaga kompatibilitas standar Eloquent ORM.
            $table->foreignId('recruitment_period_id')->constrained('recruitment_periods')->restrictOnDelete();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->restrictOnDelete();
            
            $table->string('application_number')->nullable()->unique();
            $table->string('status')->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->dateTime('hired_at')->nullable();
            $table->timestamps();

            // Aturan Bisnis: Satu user hanya boleh memiliki SATU lamaran dalam SATU periode rekrutmen
            $table->unique(['user_id', 'recruitment_period_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
