<?php

namespace App\Http\Requests\Auth;

use App\Support\PhoneNumberNormalizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation (normalize phone).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNumberNormalizer::normalize($this->phone),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16', 'unique:applicant_profiles,nik'],
            'phone' => ['required', 'string', 'regex:/^628[1-9][0-9]{7,11}$/', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'terms' => ['required', 'accepted'],
            'job_vacancy_id' => ['nullable', 'exists:job_vacancies,id'],
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
            'name' => 'Nama Lengkap',
            'nik' => 'NIK',
            'phone' => 'Nomor WhatsApp / HP',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'terms' => 'Persetujuan Syarat & Ketentuan',
            'job_vacancy_id' => 'Formasi Lowongan',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'phone.regex' => 'Format nomor WhatsApp / HP tidak valid. Gunakan format nomor Indonesia (contoh: 081234567890).',
            'phone.unique' => 'Nomor WhatsApp / HP ini sudah terdaftar.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.regex' => 'Password harus mengandung kombinasi huruf dan angka.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan serta Kebijakan Privasi.',
        ];
    }
}
