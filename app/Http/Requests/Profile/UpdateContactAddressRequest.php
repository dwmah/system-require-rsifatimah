<?php

namespace App\Http\Requests\Profile;

use App\Support\PhoneNumberNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactAddressRequest extends FormRequest
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

        // Convert checkbox to boolean
        $this->merge([
            'same_as_ktp' => $this->boolean('same_as_ktp'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^628[1-9][0-9]{7,11}$/',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'address_ktp' => ['required', 'string'],
            'same_as_ktp' => ['nullable', 'boolean'],
            'address_domicile' => [
                'nullable',
                'string',
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
