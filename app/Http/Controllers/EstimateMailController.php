<?php

namespace App\Http\Controllers;

use App\Http\Requests\EstimateMailRequest;
use App\Mail\EstimateMail;
use App\Models\EmailSend;
use App\Models\Estimate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Rebuilds estimate_mail.php — emails an estimate's PDF+Excel to a
 * client. The optional measurement-sheet PDF attachment from legacy
 * isn't ported (that print view doesn't exist yet in this app).
 */
class EstimateMailController extends Controller
{
    public function create(Estimate $estimate): View
    {
        Gate::authorize('send-email');

        return view('estimates.mail', compact('estimate'));
    }

    public function store(EstimateMailRequest $request, Estimate $estimate): RedirectResponse
    {
        Gate::authorize('send-email');

        Mail::to($request->validated('email'))
            ->send(new EstimateMail($estimate, $request->validated('client_name')));

        EmailSend::create([
            'email' => $request->validated('email'),
            'client_name' => $request->validated('client_name'),
            'file_name' => "estimate-{$estimate->id}.pdf",
            'measurement' => '',
            'all_id' => $estimate->id,
            'date' => now()->toDateString(),
            'type' => 'Estimate',
        ]);

        return redirect()->route('estimates.index')->with('status', 'Estimate has been emailed.');
    }
}
