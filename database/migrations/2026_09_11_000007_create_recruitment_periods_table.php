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
        Schema::create('recruitment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->dateTime('registration_start_at');
            $table->dateTime('registration_end_at');
            $table->string('status')->default('draft');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            // Index untuk optimasi pencarian periode rekrutmen aktif
            $table->index(['status', 'registration_start_at', 'registration_end_at'], 'recruitment_periods_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_periods');
    }
};
