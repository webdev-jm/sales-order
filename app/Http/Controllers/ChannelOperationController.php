<?php

namespace App\Http\Controllers;

use App\Models\ChannelOperation;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Paf;

class ChannelOperationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('pages.channel-operations.index');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function list(Request $request) {

        $search = trim($request->input('search'));
        $start_date = trim($request->input('start-date'));
        $end_date = trim($request->input('end-date'));

        $channel_operations = ChannelOperation::orderBy('date', 'DESC') 
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($query1) use($search) {
                    $query1->whereHas('branch_login', function($qry) use($search) {
                        $qry->whereHas('branch', function($qry1) use($search) {
                            $qry1->where('branch_code', 'like', '%'.$search.'%')
                                ->orWhere('branch_name', 'like', '%'.$search.'%')
                                ->orWhereHas('account', function($qry2) use($search) {
                                    $qry2->where('short_name', 'like', '%'.$search.'%');
                                });
                        })
                        ->orWhereHas('user', function($qry1) use($search) {
                            $qry1->where('firstname', 'like', '%'.$search.'%')
                                ->orWhere('lastname', 'like', '%'.$search.'%');
                        });
                    })
                    ->orWhere('status', 'like', '%'.$search.'%');
                });
            })
            ->when(!empty($start_date), function($query) use($start_date) {
                $query->where('date', '>=', $start_date);
            })
            ->when(!empty($end_date), function($query) use($end_date) {
                $query->where('date', '<=', $end_date);
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.channel-operations.list')->with([
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'channel_operations' => $channel_operations,
        ]);
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function show(int|string $id) {
        $channel_operation = ChannelOperation::findOrfail($id);

        return view('pages.channel-operations.show')->with([
            'channel_operation' => $channel_operation
        ]);
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\Http\Response
     */
    public function print(int|string $id) {
        $channel_operation = ChannelOperation::findOrFail($id);
        $branch_login = $channel_operation->branch_login;
        $merch_updates = $channel_operation->merch_updates->first();
        $trade_displays = $channel_operation->trade_displays->first();
        $trade_marketing_activities = $channel_operation->trade_marketing_activities->first();
        $paf = Paf::where('PAFNo', $trade_marketing_activities->paf_number ?? 'none')
            ->first();
        $extra_displays = $channel_operation->extra_displays->first();
        $competetive_reports = $channel_operation->competetive_reports;

        $pdf = PDF::loadview('pages.coe.print', [
            'channel_operation' => $channel_operation,
            'branch_login' => $branch_login,
            'merch_updates' => $merch_updates,
            'trade_displays' => $trade_displays,
            'trade_marketing_activities' => $trade_marketing_activities,
            'paf' => $paf,
            'extra_displays' => $extra_displays,
            'competetive_reports' => $competetive_reports,
        ]);

        return $pdf->stream('coe-'.$channel_operation->id.'-'.time().'.pdf');

        // return view('pages.coe.print')->with([
        //     'channel_operation' => $channel_operation,
        //     'branch_login' => $branch_login,
        //     'merch_updates' => $merch_updates,
        //     'trade_displays' => $trade_displays,
        //     'trade_marketing_activities' => $trade_marketing_activities,
        //     'paf' => $paf,
        //     'extra_displays' => $extra_displays,
        //     'competetive_reports' => $competetive_reports,
        // ]);
    }
}
