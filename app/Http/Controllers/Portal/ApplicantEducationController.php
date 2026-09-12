<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ApplicantEducationRequest;
use App\Models\ApplicantEducation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ApplicantEducationController extends Controller
{
    /**
     * Store a newly created education record.
     */
    public function store(ApplicantEducationRequest $request): RedirectResponse
    {
        $profile = $request->user()->applicantProfile;

        $profile->educations()->create($request->validated());

        return redirect()->route('profile.show')->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    /**
     * Update the specified education record.
     */
    public function update(ApplicantEducationRequest $request, ApplicantEducation $education): RedirectResponse
    {
        Gate::authorize('update', $education);

        $education->update($request->validated());

        return redirect()->route('profile.show')->with('success', 'Riwayat pendidikan berhasil diperbarui.');
    }

    /**
     * Remove the specified education record.
     */
    public function destroy(ApplicantEducation $education): RedirectResponse
    {
        Gate::authorize('delete', $education);

        $education->delete();

        return redirect()->route('profile.show')->with('success', 'Riwayat pendidikan berhasil dihapus.');
    }
}
