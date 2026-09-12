<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateContactAddressRequest;
use App\Http\Requests\Profile\UpdatePersonalDataRequest;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'applicantProfile.licenses',
            'applicantProfile.certifications',
            'applicantProfile.workExperiences',
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
}
