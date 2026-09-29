<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplicantEducationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentYear = now()->year;

        return [
            'education_level' => ['required', 'string', 'max:50'],
            'institution_name' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'accreditation' => ['nullable', 'string', 'max:20'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'graduation_year' => ['nullable', 'integer', 'min:1970', "max:{$currentYear}"],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'education_level' => 'Jenjang Pendidikan',
            'institution_name' => 'Nama Institusi / Universitas',
            'study_program' => 'Program Studi / Jurusan',
            'accreditation' => 'Akreditasi',
            'gpa' => 'IPK / Nilai Rata-rata',
            'graduation_year' => 'Tahun Kelulusan',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $currentYear = now()->year;

        return [
            'education_level.required' => 'Jenjang pendidikan wajib diisi.',
            'institution_name.required' => 'Nama institusi wajib diisi.',
            'study_program.required' => 'Program studi wajib diisi.',
            'gpa.min' => 'IPK minimal adalah 0.00.',
            'gpa.max' => 'IPK maksimal adalah 4.00.',
            'graduation_year.max' => "Tahun kelulusan tidak boleh melebihi tahun sekarang ({$currentYear}).",
        ];
    }
}
