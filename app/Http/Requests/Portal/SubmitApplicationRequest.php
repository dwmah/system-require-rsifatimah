<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class SubmitApplicationRequest extends FormRequest
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
            'declaration' => ['required', 'accepted'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'declaration.required' => 'Anda wajib menyetujui pernyataan kebenaran data dan berkas lamaran.',
            'declaration.accepted' => 'Anda wajib menyetujui pernyataan kebenaran data dan berkas lamaran.',
        ];
    }
}
