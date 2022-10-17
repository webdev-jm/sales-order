<?php

namespace App\Http\Controllers;

use App\Models\AccountProductReference;
use App\Http\Requests\StoreAccountProductReferenceRequest;
use App\Http\Requests\UpdateAccountProductReferenceRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AccountProductReferenceImport;

use App\Http\Traits\GlobalTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class AccountProductReferenceController extends Controller
{
    use GlobalTrait;

    public $settings;

    public function __construct() {
        $this->settings = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $account_product_references = AccountProductReference::AccountProductReferenceSearch($search, $this->settings->data_per_page);

        return view('account-references.index')->with([
            'search' => $search,
            'account_product_references' => $account_product_references
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('account-references.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountProductReferenceRequest $request)
    {
        $account_product_reference = new AccountProductReference([
            'account_id' => $request->account_id,
            'product_id' => $request->product_id,
            'account_reference' => $request->account_reference,
            'description' => $request->description,
        ]);
        $account_product_reference->save();

        // logs
        activity('create')
        ->performedOn($account_product_reference)
        ->log(':causer.firstname :causer.lastname has created account product reference :subject.account_reference');

        return redirect()->route('account-reference.index')->with([
            'message_success' => 'Account product reference '.$account_product_reference->account_reference.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountProductReference  $accountProductReference
     * @return \Illuminate\Http\Response
     */
    public function show(AccountProductReference $accountProductReference)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountProductReference  $accountProductReference
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $account_product_reference = AccountProductReference::findOrFail($id);

        return view('account-references.edit')->with([
            'account_product_reference' => $account_product_reference
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountProductReference  $accountProductReference
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountProductReferenceRequest $request, $id)
    {
        $account_product_reference = AccountProductReference::findOrFail($id);

        $old = $account_product_reference->getOriginal();

        $account_product_reference->update([
            'account_id' => $request->account_id,
            'product_id' => $request->product_id,
            'account_reference' => $request->account_reference,
            'description' => $request->description,
        ]);

        // logs
        activity('update')
        ->performedOn($account_product_reference)
        ->withProperties([
            'old' => $old,
            'changes' => $account_product_reference->getChanges()
        ])
        ->log(':causer.firstname :causer.lastname has updated account product reference :subject.account_reference .');

        return back()->with([
            'message_success' => 'Account product reference '.$account_product_reference->account_reference.' was updated.'
        ]);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountProductReference  $accountProductReference
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountProductReference $accountProductReference)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new AccountProductReferenceImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded account product references.');

        return back()->with([
            'message_success' => 'Account Product References has been uploaded.'
        ]);
    }
}
