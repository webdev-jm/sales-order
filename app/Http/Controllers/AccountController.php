<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Discount;
use App\Models\Company;
use App\Models\InvoiceTerm;
use App\Models\PriceCode;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountImport;

use App\Http\Traits\GlobalTrait;

class AccountController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct()
    {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request): View
    {
        $search   = trim($request->input('search'));
        $accounts = Account::AccountSearch($search, $this->setting->data_per_page);

        return view('accounts.index')->with([
            'accounts' => $accounts,
            'search'   => $search,
        ]);
    }

    public function create(): View
    {
        return view('accounts.create')->with($this->getAccountFormData());
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $account = Account::create($request->validated());

        activity('create')
            ->performedOn($account)
            ->log(':causer.firstname :causer.lastname has created account [ :subject.account_code ] :subject.account_name');

        return redirect()->route('account.index')->with([
            'message_success' => 'Account ' . $account->account_code . ' was created.',
        ]);
    }

    public function show($id): View
    {
        $account = Account::findOrFail(decrypt($id));

        return view('accounts.show')->with([
            'account' => $account,
        ]);
    }

    public function edit($id): View
    {
        $account = Account::findOrFail(decrypt($id));

        return view('accounts.edit')->with(
            array_merge(['account' => $account], $this->getAccountFormData())
        );
    }

    public function update(UpdateAccountRequest $request, $id): RedirectResponse
    {
        $account      = Account::findOrFail(decrypt($id));
        $account_name = '[' . $account->account_code . '] ' . $account->account_name;

        $changes_arr['old'] = $account->getOriginal();
        $account->update($request->validated());
        $changes_arr['changes'] = $account->getChanges();

        activity('update')
            ->performedOn($account)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated account [ :subject.account_code ] :subject.account_name .');

        return back()->with([
            'message_success' => 'Account ' . $account_name . ' was updated',
        ]);
    }

    public function destroy(Account $account): void
    {
        //
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'upload_file' => ['required', 'mimes:xlsx'],
        ]);

        Excel::import(new AccountImport, $request->upload_file);

        activity('upload')
            ->log(':causer.firstname :causer.lastname has uploaded accounts');

        return back()->with([
            'message_success' => 'Accounts has been uploaded.',
        ]);
    }

    public function ajax(Request $request)
    {
        $response = Account::AccountAjax($request->search);
        return response()->json($response);
    }

    public function getAjax($id)
    {
        $account = Account::findOrFail($id);
        return response()->json($account);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the dropdown option arrays shared by create() and edit().
     * Returns an associative array ready to be passed directly to view()->with().
     */
    private function getAccountFormData(): array
    {
        $discount_arr = Discount::orderBy('company_id', 'ASC')
            ->get()
            ->mapWithKeys(fn($d) => [$d->id => '[' . $d->company->name . '] ' . $d->discount_code . ' - ' . $d->description])
            ->all();

        $companies_arr = Company::orderBy('name', 'DESC')
            ->get()
            ->mapWithKeys(fn($c) => [$c->id => $c->name])
            ->all();

        $price_codes_arr = PriceCode::select('code')
            ->distinct()
            ->get()
            ->mapWithKeys(fn($p) => [$p->code => $p->code])
            ->all();

        $invoice_terms_arr = InvoiceTerm::orderBy('term_code', 'ASC')
            ->get()
            ->mapWithKeys(fn($t) => [$t->id => '[' . $t->term_code . '] ' . $t->description])
            ->all();

        return [
            'discounts'     => $discount_arr,
            'companies'     => $companies_arr,
            'price_codes'   => $price_codes_arr,
            'invoice_terms' => $invoice_terms_arr,
        ];
    }
}
