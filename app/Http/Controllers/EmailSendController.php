<?php

namespace App\Http\Controllers;

use App\Models\EmailSend;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Read-only log of sent report emails — rebuilds email_send.php. The
 * sidebar's "Send Email" link points here in legacy, not at a
 * compose form (composing only happens from a specific estimate — see
 * EstimateMailController).
 */
class EmailSendController extends Controller
{
    public function index(): View
    {
        Gate::authorize('send-email');

        $emailSends = EmailSend::orderByDesc('id')->paginate(20);

        return view('email-sends.index', compact('emailSends'));
    }
}
