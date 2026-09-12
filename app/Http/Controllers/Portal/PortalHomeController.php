<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalHomeController extends Controller
{
    /**
     * Display the temporary authenticated applicant portal home shell.
     */
    public function index(ProfileCompletenessService $completenessService): View
    {
        $user = Auth::user();

        return view('portal.home', [
            'user' => $user,
            'percentage' => $completenessService->percentage($user),
            'isComplete' => $completenessService->isComplete($user),
            'missingItems' => $completenessService->missingItems($user),
        ]);
    }
}
