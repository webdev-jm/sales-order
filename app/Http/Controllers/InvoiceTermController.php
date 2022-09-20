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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));
        $invoice_terms = InvoiceTerm::InvoiceTermSearch($search, $this->setting->data_per_page);
        return view('invoice-terms.index')->with([
            'invoice_terms' => $invoice_terms,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('invoice-terms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInvoiceTermRequest  $request
     * @return \Illuminate\Http\Response
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

        return redirect()->route('invoice-term.index')->with([
            'message_success' => 'Invoice Term '.$invoice_term->term_code.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceTerm  $invoiceTerm
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceTerm $invoiceTerm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceTerm  $invoiceTerm
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice_term = InvoiceTerm::findOrFail($id);
        return view('invoice-terms.edit')->with([
            'invoice_term' => $invoice_term
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceTermRequest  $request
     * @param  \App\Models\InvoiceTerm  $invoiceTerm
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInvoiceTermRequest $request, $id)
    {
        $invoice_term = InvoiceTerm::findOrFail($id);
        $invoice_term_code = $invoice_term->term_code;
        $invoice_term->update([
            'term_code' => $request->term_code,
            'description' => $request->description,
            'discount' => $request->discount,
            'discount_days' => $request->discount_days,
            'due_days' => $request->due_days
        ]);

        return back()->with([
            'message_success' => 'Invoice Term '.$invoice_term_code.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceTerm  $invoiceTerm
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceTerm $invoiceTerm)
    {
        //
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx'
            ]
        ]);

        Excel::import(new InvoiceTermImport, $request->upload_file);

        return back()->with([
            'message_success' => 'Invoice Terms has been uploaded.'
        ]);
    }
}
