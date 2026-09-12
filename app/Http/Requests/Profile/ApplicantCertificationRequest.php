<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantCertificationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:100'],
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
            'name' => 'Nama Sertifikasi / Pelatihan',
            'issuer' => 'Penerbit / Penyelenggara',
            'certificate_number' => 'Nomor Sertifikat',
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
            'name.required' => 'Nama sertifikasi / pelatihan wajib diisi.',
            'issued_at.required_with' => 'Tanggal terbit wajib diisi apabila tanggal kedaluwarsa dicantumkan.',
            'expires_at.after_or_equal' => 'Tanggal kedaluwarsa harus sama atau setelah tanggal terbit.',
        ];
    }
}
