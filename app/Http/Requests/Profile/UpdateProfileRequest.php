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
            'photo' => 'Pas Foto Formal',
            'name' => 'Nama Lengkap',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'gender' => 'Jenis Kelamin',
            'email' => 'Alamat Email',
            'phone' => 'Nomor WhatsApp / HP',
            'address_ktp' => 'Alamat KTP',
            'address_domicile' => 'Alamat Domisili',
            'same_as_ktp' => 'Sama dengan KTP',
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
            'photo.image' => 'Berkas pas foto harus berupa file gambar valid.',
            'photo.mimes' => 'Format pas foto yang diperbolehkan hanya JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran pas foto maksimal adalah 5 MB.',
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
        ];
    }
}
