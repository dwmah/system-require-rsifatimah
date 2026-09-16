<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateContactAddressRequest;
use App\Http\Requests\Profile\UpdatePersonalDataRequest;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the applicant profile page.
     */
    public function show(Request $request, ProfileCompletenessService $completenessService): View
    {
        $user = $request->user();

        $user->loadMissing([
            'applicantProfile.educations',
        ]);

        $percentage = $completenessService->percentage($user);
        $isComplete = $completenessService->isComplete($user);
        $missingItems = $completenessService->missingItems($user);
        $details = $completenessService->details($user);

        return view('portal.profile.show', [
            'user' => $user,
            'profile' => $user->applicantProfile,
            'percentage' => $percentage,
            'isComplete' => $isComplete,
            'missingItems' => $missingItems,
            'details' => $details,
        ]);
    }

    /**
     * Update the personal data (atomic transaction).
     */
    public function updatePersonal(UpdatePersonalDataRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'name' => $validated['name'],
            ]);

            $user->applicantProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'birth_place' => $validated['birth_place'],
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'],
                ]
            );
        });

        return redirect()->route('profile.show')->with('success', 'Data pribadi berhasil diperbarui.');
    }

    /**
     * Update the contact and address data (atomic transaction).
     */
    public function updateContact(UpdateContactAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $sameAsKtp = (bool) ($validated['same_as_ktp'] ?? false);
        $addressDomicile = $sameAsKtp ? $validated['address_ktp'] : ($validated['address_domicile'] ?? null);

        DB::transaction(function () use ($user, $validated, $sameAsKtp, $addressDomicile) {
            $user->update([
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            $user->applicantProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address_ktp' => $validated['address_ktp'],
                    'same_as_ktp' => $sameAsKtp,
                    'address_domicile' => $addressDomicile,
                ]
            );
        });

        return redirect()->route('profile.show')->with('success', 'Informasi alamat dan kontak berhasil diperbarui.');
    }

    /**
     * Upload / perbarui foto profil pelamar.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ], [
            'photo.required' => 'Silakan pilih berkas foto yang ingin diunggah.',
            'photo.image' => 'Berkas harus berupa file gambar valid.',
            'photo.mimes' => 'Format foto yang diperbolehkan hanya JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal adalah 5 MB.',
        ]);

        try {
            $user = $request->user();
            $file = $request->file('photo');

            if (! $file || ! $file->isValid()) {
                return redirect()->route('profile.show')->withErrors(['photo' => 'Berkas foto tidak valid atau gagal diunggah.']);
            }

            $storedPath = $file->store('profile-photos', 'public');

            $profile = $user->applicantProfile()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $user->registration_number ?? (string) rand(1000000000000000, 9999999999999999),
                    'same_as_ktp' => false,
                ]
            );

            // Hapus foto lama jika ada di storage publik
            if ($profile->photo_path && Storage::disk('public')->exists($profile->photo_path)) {
                Storage::disk('public')->delete($profile->photo_path);
            }

            $profile->update([
                'photo_path' => $storedPath,
            ]);

            return redirect()->route('profile.show')->with('success', 'Pas foto profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Profile photo upload error: ' . $e->getMessage());

            return redirect()->route('profile.show')->withErrors(['photo' => 'Terjadi kendala saat menyimpan foto. Silakan coba lagi.']);
        }
    }

    /**
     * Hapus foto profil pelamar.
     */
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->applicantProfile;

        if ($profile && $profile->photo_path) {
            if (Storage::disk('public')->exists($profile->photo_path)) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->update([
                'photo_path' => null,
            ]);
        }

        return redirect()->route('profile.show')->with('success', 'Foto profil berhasil dihapus.');
    }
}
