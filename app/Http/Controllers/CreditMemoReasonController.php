<?php

namespace App\Http\Controllers;

use App\Models\CreditMemoReason;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Traits\GlobalTrait;

use App\Http\Requests\StoreCreditMemoReasonRequest;
use App\Http\Requests\UpdateCreditMemoReasonRequest;

class CreditMemoReasonController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $search = trim($request->get('search'));

        $cm_reasons = CreditMemoReason::orderBy('created_at', 'DESC')
            ->paginate($this->setting->item_per_page)
            ->onEachSide(1)->appends(request()->query());

        return view('credit-memo-reasons.index')->with([
            'cm_reasons' => $cm_reasons,
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
        return view('credit-memo-reasons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCreditMemoReasonRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCreditMemoReasonRequest $request): RedirectResponse
    {
        $cm_reason = new CreditMemoReason([
            'reason_code' => $request->reason_code,
            'reason_description' => $request->reason_description,
        ]);
        $cm_reason->save();

        // logs
        activity('created')
            ->performedOn($cm_reason)
            ->log('Created new credit memo reason: '.$cm_reason->reason_code.' - '.$cm_reason->reason_description);

        return redirect()->route('cm-reason.index')->with([
            'message_success' => 'New credit memo reason created successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CreditMemoReason  $creditMemoReason
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $cm_reason = CreditMemoReason::findOrFail($id);
        return view('credit-memo-reasons.show')->with([
            'cm_reason' => $cm_reason
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CreditMemoReason  $creditMemoReason
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $cm_reason = CreditMemoReason::findOrFail($id);

        return view('credit-memo-reasons.edit')->with([
            'cm_reason' => $cm_reason
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CreditMemoReason  $creditMemoReason
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCreditMemoReasonRequest $request, $id): RedirectResponse
    {
        $cm_reason = CreditMemoReason::findOrFail($id);

        $changes_arr['old'] = $cm_reason->getOriginal();

        $cm_reason->update([
            'reason_code' => $request->reason_code,
            'reason_description' => $request->reason_description,
        ]);

        $changes_arr['changes'] = $cm_reason->getChanges();

        // logs
        activity('updated')
            ->performedOn($cm_reason)
            ->withProperties($changes_arr)
            ->log('Updated credit memo reason: '.$cm_reason->reason_code.' - '.$cm_reason->reason_description);

        return back()->with([
            'message_success' => 'Credit memo reason updated successfully.'
        ]);
    }

}
