<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'email' => ['required_without:identifier', 'nullable', 'string', 'email'],
            'identifier' => ['required_without:email', 'nullable', 'string', 'email'],
        ], [
            'email.required_without' => 'Alamat email terdaftar wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'identifier.required_without' => 'Alamat email terdaftar wajib diisi.',
            'identifier.email' => 'Format alamat email tidak valid.',
        ]);

        $emailInput = trim((string) ($request->input('email') ?? $request->input('identifier')));

        $user = User::where('email', $emailInput)->where('is_active', true)->first();

        if ($user) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        return back()->with(
            'status',
            'Jika data tersebut terdaftar dan akun masih aktif, instruksi reset password akan dikirim ke email akun Anda.'
        );
    }
}
