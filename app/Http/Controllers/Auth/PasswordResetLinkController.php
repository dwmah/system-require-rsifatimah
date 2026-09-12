<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ], [
            'identifier.required' => 'Email atau Nomor WhatsApp wajib diisi.',
        ]);

        $rawIdentifier = trim((string) $request->input('identifier'));
        $normalizedPhone = PhoneNumberNormalizer::normalize($rawIdentifier);

        $user = null;

        if (filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $rawIdentifier)->where('is_active', true)->first();
        } elseif ($normalizedPhone) {
            $user = User::where('phone', $normalizedPhone)->where('is_active', true)->first();
        }

        if ($user) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        return back()->with(
            'status',
            'Jika data tersebut terdaftar dan akun masih aktif, instruksi reset password akan dikirim ke email akun Anda.'
        );
    }
}
