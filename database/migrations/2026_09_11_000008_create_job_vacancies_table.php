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
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_period_id')->constrained('recruitment_periods')->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('unit');
            $table->string('employment_type');
            $table->unsignedInteger('quota');
            $table->string('location');
            $table->longText('description')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->dateTime('open_at');
            $table->dateTime('close_at');
            $table->string('status')->default('draft');
            $table->timestamps();

            // Index untuk optimasi query lowongan aktif pada periode rekrutmen
            $table->index(['recruitment_period_id', 'status', 'open_at', 'close_at'], 'job_vacancies_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
