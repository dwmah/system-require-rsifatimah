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
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            
            // Invariant: vacancy_document_requirement_id harus milik job_vacancy yang dilamar oleh job_application
            $table->foreignId('vacancy_document_requirement_id')->constrained('vacancy_document_requirements')->restrictOnDelete();
            
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->string('verification_status')->nullable();
            $table->text('verification_note')->nullable();
            $table->timestamps();

            // Satu lamaran hanya boleh mempunyai satu dokumen aktif untuk satu jenis persyaratan dokumen
            $table->unique(['job_application_id', 'vacancy_document_requirement_id'], 'app_docs_app_req_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
