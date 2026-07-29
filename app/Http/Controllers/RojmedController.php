<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountDetail;
use Illuminate\Contracts\View\View;

class RojmedController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Account::class);

        $date = request('date', now()->toDateString());

        $openingCredit = (float) AccountDetail::where('type', 'Credit')->where('date', '<', $date)->sum('amount');
        $openingDebit = (float) AccountDetail::where('type', 'Debit')->where('date', '<', $date)->sum('amount');

        $entries = AccountDetail::with('account')->whereDate('date', $date)->orderByDesc('id')->get();

        $closingCredit = $openingCredit + (float) $entries->where('type', 'Credit')->sum('amount');
        $closingDebit = $openingDebit + (float) $entries->where('type', 'Debit')->sum('amount');

        return view('rojmed.index', [
            'date' => $date,
            'openingDebit' => max($openingDebit - $openingCredit, 0),
            'openingCredit' => max($openingCredit - $openingDebit, 0),
            'entries' => $entries,
            'closingDebit' => max($closingDebit - $closingCredit, 0),
            'closingCredit' => max($closingCredit - $closingDebit, 0),
        ]);
    }
}
