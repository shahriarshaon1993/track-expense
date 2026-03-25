<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // This controller is already protected by middleware in routes, so no constructor needed
    use AuthorizesRequests;

    /**
     * Display a listing of the user's accounts.
     */
    public function index()
    {
        $userId = auth()->id(); // Get the currently authenticated user's ID

        // Retrieve accounts belonging to the user with pagination
        $accounts = Account::where('user_id', $userId)->paginate(10);

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new account.
     */
    public function create()
    {
        return view('accounts.create');
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(StoreAccountRequest $request)
    {
        // Remove thousand separators if any
        $balance = str_replace([',', '.'], '', $request->balance);

        // Check if an account with the same name already exists for this user
        $existingAccount = Account::where('user_id', auth()->id())
            ->where('name', $request->name)
            ->first();

        if ($existingAccount) {
            return redirect()->back()->with('error', 'An account with this name already exists.');
        }

        // Create a new account
        Account::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'balance' => $balance,
            'type' => $request->type,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account has been successfully added!');
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(Account $account)
    {
        // Ensure only the owner can access
        if ($account->user_id !== auth()->id()) {
            return redirect()->route('accounts.index')->with('error', 'Access denied.');
        }

        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        // Prevent duplicate account names for the same user
        if (
            Account::where('user_id', auth()->id())
                ->where('name', $request->name)
                ->where('id', '!=', $account->id)
                ->exists()
        ) {
            return redirect()->back()->with('error', 'An account with this name already exists.');
        }

        $account->update($request->only(['name', 'balance', 'type']));

        return redirect()->route('accounts.index')->with('success', 'Account has been successfully updated!');
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        try {
            $account->delete();
            return redirect()->route('accounts.index')->with('success', 'Account has been successfully deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('accounts.index')->with('error', 'Account cannot be deleted because it has related transactions.');
        }
    }
}