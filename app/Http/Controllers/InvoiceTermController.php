<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTerm;
use App\Http\Requests\StoreInvoiceTermRequest;
use App\Http\Requests\UpdateInvoiceTermRequest;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InvoiceTermImport;

use App\Http\Traits\GlobalTrait;

class InvoiceTermController extends Controller
{

    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search'));
        $invoice_terms = InvoiceTerm::InvoiceTermSearch($search, $this->setting->data_per_page);
        return view('pages.invoice-terms.index')->with([
            'invoice_terms' => $invoice_terms,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('pages.invoice-terms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreInvoiceTermRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreInvoiceTermRequest $request)
    {
        $invoice_term = new InvoiceTerm([
            'term_code' => $request->term_code,
            'description' => $request->description,
            'discount' => $request->discount,
            'discount_days' => $request->discount_days,
            'due_days' => $request->due_days
        ]);
        $invoice_term->save();

        // logs
        activity('create')
        ->performedOn($invoice_term)
        ->log(':causer.firstname :causer.lastname has created invoice term :subject.description');

        return redirect()->route('invoice-term.index')->with([
            'message_success' => 'Invoice Term '.$invoice_term->term_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  InvoiceTerm  $invoiceTerm
     * @return void
     */
    public function show(InvoiceTerm $invoiceTerm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit(int|string $id)
    {
        $invoice_term = InvoiceTerm::findOrFail($id);
        return view('pages.invoice-terms.edit')->with([
            'invoice_term' => $invoice_term
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateInvoiceTermRequest  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateInvoiceTermRequest $request, int|string $id)
    {
        $invoice_term = InvoiceTerm::findOrFail($id);
        $invoice_term_code = $invoice_term->term_code;

        $changes_arr['old'] = $invoice_term->getOriginal();

        $invoice_term->update([
            'term_code' => $request->term_code,
            'description' => $request->description,
            'discount' => $request->discount,
            'discount_days' => $request->discount_days,
            'due_days' => $request->due_days
        ]);

        $changes_arr['changes'] = $invoice_term->getChanges();

        // logs
        activity('update')
        ->performedOn($invoice_term)
        ->withProperties($changes_arr)
        ->log(':causer.firstname :causer.lastname has updated invoice term :subject.description .');

        return back()->with([
            'message_success' => 'Invoice Term '.$invoice_term_code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  InvoiceTerm  $invoiceTerm
     * @return void
     */
    public function destroy(InvoiceTerm $invoiceTerm)
    {
        //
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new InvoiceTermImport, $request->upload_file);

        // logs
        activity('upload')
        ->log(':causer.firstname :causer.lastname has uploaded invoice terms');

        return back()->with([
            'message_success' => 'Invoice Terms has been uploaded.'
        ]);
    }
}
