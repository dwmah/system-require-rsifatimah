<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ApplicantWorkExperienceRequest;
use App\Models\ApplicantWorkExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicantWorkExperienceController extends Controller
{
    /**
     * Store a newly created work experience record.
     */
    public function store(ApplicantWorkExperienceRequest $request): RedirectResponse
    {
        $profile = $request->user()->applicantProfile;

        $profile->workExperiences()->create($request->validated());

        return redirect()->route('profile.show')->with('success', 'Riwayat pengalaman kerja berhasil ditambahkan.');
    }

    /**
     * Update the specified work experience record.
     */
    public function update(ApplicantWorkExperienceRequest $request, ApplicantWorkExperience $workExperience): RedirectResponse
    {
        Gate::authorize('update', $workExperience);

        $workExperience->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Riwayat pengalaman kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified work experience record.
     */
    public function destroy(ApplicantWorkExperience $workExperience): RedirectResponse
    {
        Gate::authorize('delete', $workExperience);

        $workExperience->delete();

        return redirect()->route('profile.show')->with('success', 'Riwayat pengalaman kerja berhasil dihapus.');
    }
}
