<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplicantLicenseRequest extends FormRequest
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
        return [
            'license_type' => ['required', 'string', 'max:100'],
            'license_number' => ['required', 'string', 'max:100'],
            'issued_at' => ['nullable', 'date', 'required_with:expires_at'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
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
            'license_type' => 'Jenis Lisensi / STR',
            'license_number' => 'Nomor Lisensi / STR',
            'issued_at' => 'Tanggal Terbit',
            'expires_at' => 'Tanggal Kedaluwarsa',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_type.required' => 'Jenis lisensi / STR wajib diisi.',
            'license_number.required' => 'Nomor lisensi / STR wajib diisi.',
            'issued_at.required_with' => 'Tanggal terbit wajib diisi apabila tanggal kedaluwarsa dicantumkan.',
            'expires_at.after_or_equal' => 'Tanggal kedaluwarsa harus sama atau setelah tanggal terbit.',
        ];
    }
}
