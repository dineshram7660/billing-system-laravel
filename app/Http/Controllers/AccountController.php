<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Rebuilds account.php/add_edit_account.php. Only account_name is
 * editable here — the account/employee/status/etc. columns the legacy
 * `account` table shares with `employee` are unused by this form (see
 * the model docblock).
 */
class AccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Account::class);

        $accounts = Account::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('account_name', 'like', "%{$search}%");
            })
            ->orderBy('account_name')
            ->paginate(20)
            ->withQueryString();

        return view('accounts.index', compact('accounts'));
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);

        $details = $account->details()->orderByDesc('date')->orderByDesc('id')->paginate(20);

        $debit = (float) $account->details()->where('type', 'Debit')->sum('amount');
        $credit = (float) $account->details()->where('type', 'Credit')->sum('amount');

        return view('accounts.show', [
            'account' => $account,
            'details' => $details,
            'balance' => $debit - $credit,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('accounts.form', ['account' => new Account]);
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        $this->authorize('create', Account::class);

        Account::create($request->validated());

        return redirect()->route('accounts.index')->with('status', 'Account added successfully.');
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.form', compact('account'));
    }

    public function update(AccountRequest $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $account->update($request->validated());

        return redirect()->route('accounts.index')->with('status', 'Account updated successfully.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('accounts.index')->with('status', 'Account deleted successfully.');
    }
}
