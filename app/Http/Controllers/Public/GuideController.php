<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentGuide;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Menampilkan panduan alur pendaftaran rekrutmen SDM RSI Fatimah Banyuwangi.
     */
    public function flow(): View
    {
        $guide = RecruitmentGuide::query()
            ->flow()
            ->active()
            ->orderBy('sort_order')
            ->first();

        return view('public.guides.flow', [
            'guide' => $guide,
        ]);
    }

    /**
     * Menampilkan panduan persyaratan umum dan ketentuan pendaftaran.
     */
    public function requirements(): View
    {
        $guide = RecruitmentGuide::query()
            ->requirements()
            ->active()
            ->orderBy('sort_order')
            ->first();

        return view('public.guides.requirements', [
            'guide' => $guide,
        ]);
    }
}
