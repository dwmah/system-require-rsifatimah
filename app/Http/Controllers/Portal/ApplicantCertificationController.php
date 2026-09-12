<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ApplicantCertificationRequest;
use App\Models\ApplicantCertification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicantCertificationController extends Controller
{
    /**
     * Store a newly created certification record.
     */
    public function store(ApplicantCertificationRequest $request): RedirectResponse
    {
        $profile = $request->user()->applicantProfile;

        $profile->certifications()->create($request->validated());

        return redirect()->route('profile.show')->with('success', 'Data sertifikasi / pelatihan berhasil ditambahkan.');
    }

    /**
     * Update the specified certification record.
     */
    public function update(ApplicantCertificationRequest $request, ApplicantCertification $certification): RedirectResponse
    {
        Gate::authorize('update', $certification);

        $certification->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Data sertifikasi / pelatihan berhasil diperbarui.');
    }

    /**
     * Remove the specified certification record.
     */
    public function destroy(ApplicantCertification $certification): RedirectResponse
    {
        Gate::authorize('delete', $certification);

        $certification->delete();

        return redirect()->route('profile.show')->with('success', 'Data sertifikasi / pelatihan berhasil dihapus.');
    }
}
