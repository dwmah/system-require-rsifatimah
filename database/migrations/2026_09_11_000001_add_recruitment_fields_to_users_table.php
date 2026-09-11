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
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->unique()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('remember_token');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['registration_number']);
            $table->dropColumn(['registration_number', 'phone', 'is_active', 'deactivated_at']);
        });
    }
};
