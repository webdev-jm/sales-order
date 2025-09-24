<?php

namespace App\Http\Controllers;

use App\Models\CreditMemo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Traits\GlobalTrait;

use App\Models\Account;
use App\Models\CreditMemoReason;

class CreditMemoController extends Controller
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
    public function index(Request $request): View
    {
        $search = trim($request->get('search'));

        $credit_memos = CreditMemo::orderBy('created_at', 'DESC')
            ->when($search, function ($query, $search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('po_number', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%");
            })
            ->paginate($this->setting->item_per_page)
            ->appends(request()->query());

        return view('credit-memos.index')->with([
            'credit_memos' => $credit_memos,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('credit-memos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditMemo  $creditMemo
     * @return \Illuminate\Http\Response
     */
    public function show(CreditMemo $creditMemo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditMemo  $creditMemo
     * @return \Illuminate\Http\Response
     */
    public function edit(CreditMemo $creditMemo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditMemo  $creditMemo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CreditMemo $creditMemo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CreditMemo  $creditMemo
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditMemo $creditMemo)
    {
        //
    }
}
