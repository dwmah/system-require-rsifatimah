<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ApplicantLicenseRequest;
use App\Models\ApplicantLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicantLicenseController extends Controller
{
    /**
     * Store a newly created license record.
     */
    public function store(ApplicantLicenseRequest $request): RedirectResponse
    {
        $profile = $request->user()->applicantProfile;

        $profile->licenses()->create($request->validated());

        return redirect()->route('profile.show')->with('success', 'Data lisensi / STR berhasil ditambahkan.');
    }

    /**
     * Update the specified license record.
     */
    public function update(ApplicantLicenseRequest $request, ApplicantLicense $license): RedirectResponse
    {
        Gate::authorize('update', $license);

        $license->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Data lisensi / STR berhasil diperbarui.');
    }

    /**
     * Remove the specified license record.
     */
    public function destroy(ApplicantLicense $license): RedirectResponse
    {
        Gate::authorize('delete', $license);

        $license->delete();

        return redirect()->route('profile.show')->with('success', 'Data lisensi / STR berhasil dihapus.');
    }
}
