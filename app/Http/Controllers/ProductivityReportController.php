<?php

namespace App\Http\Controllers;

use App\Models\ProductivityReport;
use App\Models\ProductivityReportData;
use App\Http\Requests\StoreProductivityReportRequest;
use App\Http\Requests\UpdateProductivityReportRequest;

use Illuminate\Support\Facades\Session;

class ProductivityReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Session::forget('productivity_report_data');

        return view('productivity-reports.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productivity-reports.upload');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductivityReportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductivityReportRequest $request)
    {
        $data = Session::get('productivity_report_data');
        if(!empty($data)) {
            $productivity_report = new ProductivityReport([
                'user_id' => auth()->user()->id,
                'year' => $request->year,
                'month' => $request->month,
                'week' => $request->week,
            ]);
            $productivity_report->save();

            // Data
            foreach($data as $val) {
                if(!empty($val['branch_id']) && !empty($val['classification_id'])) {
                    $productivity_report_data = new ProductivityReportData([
                        'productivity_report_id' => $productivity_report->id,
                        'branch_id' => $val['branch_id'],
                        'classification_id' => $val['classification_id'],
                        'date' => $val['date'],
                        'salesman' => $val['salesman'],
                        'visited' => $val['visited'],
                        'sales' => $val['sales']
                    ]);
                    $productivity_report_data->save();
                }
            }
        }

        return redirect()->route('productivity-report.index')->with([
            'message_success' => 'Productivity report has been uploaded.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductivityReport  $productivityReport
     * @return \Illuminate\Http\Response
     */
    public function show(ProductivityReport $productivityReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductivityReport  $productivityReport
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductivityReport $productivityReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductivityReportRequest  $request
     * @param  \App\Models\ProductivityReport  $productivityReport
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductivityReportRequest $request, ProductivityReport $productivityReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductivityReport  $productivityReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductivityReport $productivityReport)
    {
        //
    }
}
