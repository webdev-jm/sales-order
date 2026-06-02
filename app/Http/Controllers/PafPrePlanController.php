<?php

namespace App\Http\Controllers;

use App\Exports\PrePlanTemplateExport;
use App\Models\PafPrePlan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PafPrePlanController extends Controller
{
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new PrePlanTemplateExport, 'pre-plan-upload-template.xlsx');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $search = trim($request->input('search', ''));

        $pre_plans = PafPrePlan::with(['account', 'support_type'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('pre_plan_number', 'LIKE', "%{$search}%")
                      ->orWhere('year', 'LIKE', "%{$search}%")
                      ->orWhereHas('account', function ($q) use ($search) {
                          $q->where('account_name', 'LIKE', "%{$search}%")
                            ->orWhere('short_name', 'LIKE', "%{$search}%")
                            ->orWhere('account_code', 'LIKE', "%{$search}%");
                      });
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.pre-plans.index')->with([
            'pre_plans' => $pre_plans,
            'search'    => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function show($id): \Illuminate\View\View
    {
        $pre_plan = PafPrePlan::findOrFail($id);

        $pre_plan_details = $pre_plan->pre_plan_details()->paginate(10);
        $total_amount = $pre_plan->pre_plan_details()->sum('amount');

        return view('pages.pre-plans.show')->with([
            'pre_plan'         => $pre_plan,
            'pre_plan_details' => $pre_plan_details,
            'total_amount'     => $total_amount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function edit(PafPrePlan $pafPrePlan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PafPrePlan $pafPrePlan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PafPrePlan  $pafPrePlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(PafPrePlan $pafPrePlan)
    {
        //
    }
}
