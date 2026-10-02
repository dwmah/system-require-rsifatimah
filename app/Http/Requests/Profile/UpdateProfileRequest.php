<?php

namespace App\Http\Requests\Profile;

use App\Support\PhoneNumberNormalizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNumberNormalizer::normalize($this->phone),
            ]);
        }

        $this->merge([
            'same_as_ktp' => $this->boolean('same_as_ktp'),
            'remove_photo' => $this->boolean('remove_photo'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        $currentYear = now()->year;

        return [
            // Pas Foto
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
            // Data Pribadi
            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'string', 'in:Laki-laki,Perempuan'],
            // Kontak & Alamat
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'required', 'string',
                'regex:/^628[1-9][0-9]{7,11}$/',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'address_ktp' => ['required', 'string'],
            'same_as_ktp' => ['nullable', 'boolean'],
            'address_domicile' => [
                'nullable', 'string',
                Rule::requiredIf(! $this->boolean('same_as_ktp')),
            ],
            // Riwayat Pendidikan Formal Terakhir
            'education_level' => ['nullable', 'string', 'max:50'],
            'institution_name' => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'min:1970', "max:{$currentYear}"],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'accreditation' => ['nullable', 'string', 'max:20'],
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
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'gender' => 'Jenis Kelamin',
            'email' => 'Alamat Email',
            'phone' => 'Nomor WhatsApp / HP',
            'address_ktp' => 'Alamat KTP',
            'address_domicile' => 'Alamat Domisili',
            'same_as_ktp' => 'Sama dengan KTP',
            'education_level' => 'Jenjang Pendidikan',
            'institution_name' => 'Nama Institusi / Universitas',
            'study_program' => 'Program Studi / Jurusan',
            'graduation_year' => 'Tahun Kelulusan',
            'gpa' => 'IPK / Nilai Rata-rata',
            'accreditation' => 'Akreditasi',
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
            'name.required' => 'Nama lengkap wajib diisi.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.regex' => 'Format nomor WhatsApp / HP tidak valid. Gunakan format nomor Indonesia (contoh: 081234567890).',
            'phone.unique' => 'Nomor WhatsApp / HP sudah digunakan oleh pengguna lain.',
            'address_ktp.required' => 'Alamat sesuai KTP wajib diisi.',
            'address_domicile.required' => 'Alamat domisili wajib diisi jika berbeda dengan KTP.',
            'gpa.min' => 'IPK minimal adalah 0.00.',
            'gpa.max' => 'IPK maksimal adalah 4.00.',
            'graduation_year.min' => 'Tahun kelulusan minimal tahun 1970.',
            'graduation_year.max' => "Tahun kelulusan tidak boleh melebihi tahun sekarang ({$currentYear}).",
        ];
    }
}
