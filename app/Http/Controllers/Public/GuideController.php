<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Menampilkan panduan alur pendaftaran rekrutmen SDM RSI Fatimah Banyuwangi.
     */
    public function flow(): View
    {
        return view('public.guides.flow');
    }

    /**
     * Menampilkan panduan persyaratan umum dan ketentuan pendaftaran.
     */
    public function requirements(): View
    {
        return view('public.guides.requirements');
    }
}
