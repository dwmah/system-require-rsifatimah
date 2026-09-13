<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\RecruitmentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicantNotificationController extends Controller
{
    /**
     * Tampilkan halaman daftar notifikasi pelamar.
     */
    public function index(): View
    {
        $user = Auth::user();
        $notifications = $user->recruitmentNotifications()->paginate(15);

        return view('portal.notifications.index', [
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Tandai notifikasi spesifik sebagai telah dibaca.
     */
    public function markAsRead(RecruitmentNotification $notification): RedirectResponse
    {
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi user sebagai telah dibaca.
     */
    public function markAllAsRead(): RedirectResponse
    {
        $user = Auth::user();

        $user->recruitmentNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Seluruh notifikasi telah ditandai dibaca.');
    }
}
