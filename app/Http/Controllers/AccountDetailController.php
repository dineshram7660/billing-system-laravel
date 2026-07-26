<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountDetailRequest;
use App\Models\Account;
use App\Models\AccountDetail;
use Illuminate\Http\RedirectResponse;

class AccountDetailController extends Controller
{
    public function store(AccountDetailRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('create', AccountDetail::class);

        $account->details()->create($request->validated());

        return redirect()->route('accounts.show', $account)->with('status', 'Transaction added successfully.');
    }

    public function destroy(Account $account, AccountDetail $detail): RedirectResponse
    {
        $this->authorize('delete', $detail);

        $detail->delete();

        return redirect()->route('accounts.show', $account)->with('status', 'Transaction deleted successfully.');
    }
}
