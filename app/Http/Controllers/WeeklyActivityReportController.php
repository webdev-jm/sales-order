<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OrganizationStructure;
use App\Models\Area;
use App\Models\WeeklyActivityReport;
use App\Models\WeeklyActivityReportArea;
use App\Models\WeeklyActivityReportBranch;
use App\Models\WeeklyActivityReportObjective;
use App\Models\WeeklyActivityReportApproval;

use App\Models\UserBranchSchedule;


use App\Http\Requests\StoreWeeklyActivityReportRequest;
use App\Http\Requests\UpdateWeeklyActivityReportRequest;

use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

use App\Http\Traits\GlobalTrait;

use Illuminate\Support\Facades\Notification;
use App\Notifications\WeeklyActivityReportSubmitted;
use App\Notifications\WeeklyActivityReportApproved;
use App\Notifications\WeeklyActivityReportRejected;

class WeeklyActivityReportController extends Controller
{

    use GlobalTrait;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'primary',
        'approved' => 'success',
        'rejected' => 'danger'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $settings = $this->getSettings();

        $search = trim($request->get('search'));

        $subordinate_ids = auth()->user()->getSubordinateIds();

        $weekly_activity_reports = WeeklyActivityReport::WeeklyActivityReportSearch($search, $settings->data_per_page, $subordinate_ids);

        return view('war.index')->with([
            'search' => $search,
            'weekly_activity_reports' => $weekly_activity_reports,
            'status_arr' => $this->status_arr
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // area options
        $areas = Area::orderBy('area_code', 'ASC')
            ->get();

        $areas_arr = [
            '' => ''
        ];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }

        // areas data

        return view('war.create')->with([
            'areas' => $areas_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreWeeklyActivityReportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWeeklyActivityReportRequest $request)
    {
        $date_submitted = NULL;
        if($request->status == 'submitted') {
            $date_submitted = date('Y-m-d');
        }

        $war = new WeeklyActivityReport([
            'user_id' => auth()->user()->id,
            'area_id' => $request->area_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'week_number' => $request->week,
            'date_submitted' => $date_submitted,
            'objectives' => $request->objective,
            'highlights' => $request->highlights,
            'status' => $request->status,
        ]);
        $war->save();

        // areas
        foreach($request->area_date as $key => $date) {
            $area = new WeeklyActivityReportArea([
                'weekly_activity_report_id' => $war->id,
                'date' => $date,
                'day' => $request->area_day[$date],
                'location' => $request->area_covered[$date],
                'remarks' => $request->area_remarks[$date]
            ]);
            $area->save();

            if(!empty($request->action_points[$date])) {
                foreach($request->action_points[$date] as $branch_id => $action_point) {
                    $war_branch = new WeeklyActivityReportBranch([
                        'weekly_activity_report_area_id' => $area->id,
                        'branch_id' => $branch_id,
                        'user_branch_schedule_id' => $request->user_branch_schedule_id[$date][$branch_id],
                        'branch_login_id' => $request->branch_login_id[$date][$branch_id],
                        'status' => $request->branch_status[$date][$branch_id],
                        'action_points' => $action_point
                    ]);
                    $war_branch->save();
                }
            }
        }

        if($request->status == 'submitted') {
            // notifications
            $supervisor_id = $war->user->getImmediateSuperiorId();
            if(!empty($supervisor_id)) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new WeeklyActivityReportSubmitted($war));
                }
            }
        }

        return redirect()->route('war.index')->with([
            'message_success' => 'Weekly Activity Report was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $weekly_activity_report = WeeklyActivityReport::findOrFail($id);

        $supervisor_ids = $weekly_activity_report->user->getSupervisorIds();

        $area_status_arr = [
            'VISITED' => 'success',
            'NOT VISITED' => 'danger',
            'DEVIATION' => 'warning',
        ];

        return view('war.show')->with([
            'weekly_activity_report' => $weekly_activity_report,
            'status_arr' => $this->status_arr,
            'supervisor_ids' => $supervisor_ids,
            'area_status_arr' => $area_status_arr
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $weekly_activity_report = WeeklyActivityReport::findOrFail($id);

        // area options
        $areas = Area::orderBy('area_code', 'ASC')
        ->get();

        $areas_arr = [
            '' => ''
        ];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }

        return view('war.edit')->with([
            'areas' => $areas_arr,
            'weekly_activity_report' => $weekly_activity_report,
            'status_arr' => $this->status_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWeeklyActivityReportRequest  $request
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWeeklyActivityReportRequest $request, $id)
    {
        $weekly_activity_report = WeeklyActivityReport::findOrFail($id);

        $date_submitted = NULL;
        if($request->status == 'submitted') {
            $date_submitted = date('Y-m-d');
        }

        $weekly_activity_report->update([
            'area_id' => $request->area_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'week_number' => $request->week,
            'date_submitted' => $date_submitted,
            'highlights' => $request->highlights,
            'objevtives' => $request->objectives,
            'status' => $request->status
        ]);

        // areas
        foreach($weekly_activity_report->areas as $area) {
            $area->war_branches()->delete();
        }
        $weekly_activity_report->areas()->delete();
        foreach($request->area_date as $key => $date) {
            $area = new WeeklyActivityReportArea([
                'weekly_activity_report_id' => $weekly_activity_report->id,
                'date' => $date,
                'day' => $request->area_day[$date],
                'location' => $request->area_covered[$date],
                'remarks' => $request->area_remarks[$date]
            ]);
            $area->save();

            if(!empty($request->action_points[$date])) {
                foreach($request->action_points[$date] as $branch_id => $action_point) {
                    $war_branch = new WeeklyActivityReportBranch([
                        'weekly_activity_report_area_id' => $area->id,
                        'branch_id' => $branch_id,
                        'user_branch_schedule_id' => $request->user_branch_schedule_id[$date][$branch_id],
                        'branch_login_id' => $request->branch_login_id[$date][$branch_id],
                        'status' => $request->branch_status[$date][$branch_id],
                        'action_points' => $action_point
                    ]);
                    $war_branch->save();
                }
            }
        }

        if($request->status == 'submitted') {

            $supervisor_id = $weekly_activity_report->user->getImmediateSuperiorId();
            if(!empty($supervisor_id)) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new WeeklyActivityReportSubmitted($weekly_activity_report));
                }
            }

            return redirect()->route('war.index')->with([
                'message_success' => 'Weekly activity report has been updated.'
            ]);
        } else {
            return back()->with([
                'message_success' => 'Weekly activity report has been updated.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    public function printPDF($id) {
        $weekly_activity_report = WeeklyActivityReport::findOrFail($id);

        $pdf = PDF::loadView('war.pdf', [
            'weekly_activity_report' => $weekly_activity_report
        ]);

        return $pdf->stream('weekly-activity-report-'.$weekly_activity_report->date.'-'.time().'.pdf');
    }

    public function approval(Request $request, $id) {
        $request->validate([
            'status' => 'required',
            'remarks' => 'max:2000|required_if:status,rejected'
        ]);

        $war = WeeklyActivityReport::findOrFail($id);

        // update status
        $war->update([
            'status' => $request->status
        ]);

        // approval
        $approval = new WeeklyActivityReportApproval([
            'user_id' => auth()->user()->id,
            'weekly_activity_report_id' => $war->id,
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);
        $approval->save();

        // notification
        if($request->status == 'approved') {
            $user = $war->user;
            Notification::send($user, new WeeklyActivityReportApproved($war));
        } else {
            $user = $war->user;
            Notification::send($user, new WeeklyActivityReportRejected($war));
        }

        return back()->with([
            'message_success' => 'Weekly Activity Report has been updated'
        ]);
    }
}
