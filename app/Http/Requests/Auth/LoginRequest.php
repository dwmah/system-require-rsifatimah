<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
            'email' => ['required_without:login', 'nullable', 'string', 'email'],
            'login' => ['required_without:email', 'nullable', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
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
            'login' => 'Alamat Email',
            'password' => 'Password',
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
            'email.required_without' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'login.required_without' => 'Alamat email wajib diisi.',
            'login.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $emailInput = trim((string) ($this->input('email') ?? $this->input('login')));

        $user = User::where('email', $emailInput)->first();

        $primaryKey = $this->has('email') ? 'email' : 'login';

        if (! $user || ! Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $primaryKey => trans('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $primaryKey => 'Akun pelamar Anda sudah tidak aktif. Silakan hubungi panitia rekrutmen.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $primaryKey = $this->has('email') ? 'email' : 'login';

        throw ValidationException::withMessages([
            $primaryKey => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     * Hashed with SHA-256 to avoid storing plain identifier in cache key.
     */
    public function throttleKey(): string
    {
        $normalizedLogin = Str::lower(trim((string) ($this->input('email') ?? $this->input('login'))));

        return hash('sha256', $normalizedLogin.'|'.$this->ip());
    }
}
