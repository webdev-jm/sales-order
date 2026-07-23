<?php

namespace App\Http\Livewire\Accounts;

use App\Models\Account;
use App\Services\AccountLoginResolver;
use Livewire\Component;

/**
 * Banner shown on the sales order and PPU pages that lets the user set the
 * account new records are tagged with, without going through the home screen
 * account sign in.
 *
 * The account list is search driven rather than paginated on purpose: the
 * WithPagination trait swaps Laravel's default pagination view for a Livewire
 * one globally, which breaks the plain Blade paginators already on the sales
 * order and PPU list pages.
 */
class ActiveAccountBanner extends Component
{
    /**
     * Accounts shown at a time before the user is asked to narrow the search.
     */
    private const RESULT_LIMIT = 10;

    public $search = '';

    public $confirm_account_id;

    public $accuracy, $longitude, $latitude;

    public function openSelector(): void
    {
        $this->reset(['search', 'confirm_account_id']);
        $this->dispatchBrowserEvent('openAccountSelector');
    }

    /**
     * Ask for confirmation before the active account is replaced.
     */
    public function confirmAccount(int $account_id): void
    {
        $this->confirm_account_id = $account_id;
    }

    public function cancelConfirm(): void
    {
        $this->confirm_account_id = null;
    }

    public function switchAccount(AccountLoginResolver $resolver)
    {
        $user = auth()->user();

        if (empty($this->confirm_account_id) || !$resolver->isAssignedTo($user, (int) $this->confirm_account_id)) {
            $this->confirm_account_id = null;

            return null;
        }

        $account = Account::findOrFail($this->confirm_account_id);

        $resolver->switchTo($user, $account, [
            'longitude' => $this->longitude,
            'latitude'  => $this->latitude,
            'accuracy'  => $this->accuracy,
        ]);

        $this->confirm_account_id = null;
        $this->dispatchBrowserEvent('closeAccountSelector');

        return redirect(url()->previous());
    }

    public function render()
    {
        $resolver = app(AccountLoginResolver::class);
        $user = auth()->user();

        $account_login = $resolver->resolve();

        $search = trim($this->search);

        $accounts = $resolver->assignedAccounts($user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qry) use ($search) {
                    $qry->where('account_code', 'like', '%' . $search . '%')
                        ->orWhere('account_name', 'like', '%' . $search . '%')
                        ->orWhere('short_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('account_name')
            ->limit(self::RESULT_LIMIT + 1)
            ->get();

        // One extra row is fetched purely to detect that the list was cut short.
        $has_more = $accounts->count() > self::RESULT_LIMIT;

        return view('livewire.accounts.active-account-banner')->with([
            'account_login'   => $account_login,
            'active_account'  => $account_login->account ?? null,
            'accounts'        => $accounts->take(self::RESULT_LIMIT),
            'has_more'        => $has_more,
            'confirm_account' => $this->confirm_account_id ? Account::find($this->confirm_account_id) : null,
        ]);
    }
}
