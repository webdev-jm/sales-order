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
use App\Models\WeeklyActivityReportAttachment;

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

use Illuminate\Support\Facades\Storage;

class WeeklyActivityReportController extends Controller
{

    use GlobalTrait;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'primary',
        'approved' => 'success',
        'rejected' => 'danger'
    ];

    public function list(Request $request, $id) {
        $settings = $this->getSettings();

        $search = trim($request->get('search'));

        $subordinate_ids = auth()->user()->getSubordinateIds();

        $ids = [];
        foreach($subordinate_ids as $level => $id_arr) {
            foreach($id_arr as $user_id) {
                $ids[] = $user_id;
            }
        }

        if(in_array($id, $ids) || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->id == $id) {

            // $weekly_activity_reports = WeeklyActivityReport::WeeklyActivityReportSearch($search, $settings->data_per_page, ['level_1' => [$id]]);

            $weekly_activity_reports = WeeklyActivityReport::orderBy('id', 'DESC')
                ->where('user_id', $id)
                ->when(!empty($search), function($query) use ($search) {
                    $query->where(function($qry) use($search) {
                        $qry->whereHas('user', function($qry) use ($search) {
                            $qry->where('firstname', 'like', '%'.$search.'%')
                            ->orWhere('lastname', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('area', function($qry1) use($search) {
                            $qry1->where('area_code', 'like', '%'.$search.'%')
                            ->orWhere('area_name', 'like', '%'.$search.'%');
                        })
                        ->orWhere('date_submitted', 'like', '%'.$search.'%')
                        ->orWhere('date_from', 'like', '%'.$search.'%')
                        ->orWhere('date_to', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%');
                    });
                })
                ->paginate($settings->data_per_page)->onEachSide(1)->appends(request()->query());

            return view('war.list')->with([
                'search' => $search,
                'weekly_activity_reports' => $weekly_activity_reports,
                'status_arr' => $this->status_arr,
                'user_id' => $id
            ]);

        } else {
            return redirect()->route('war.index')->with([
                'message_error' => 'You do not have the necessary access privileges to view this user\'s weekly productivity reports.'
            ]);
        }
    }

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

        $ids = [];
        foreach($subordinate_ids as $level => $id_arr) {
            foreach($id_arr as $id) {
                $ids[] = $id;
            }
        }

        $users = User::orderBy('firstname', 'ASC')
            ->whereHas('weekly_activity_reports')
            ->when(!auth()->user()->hasRole('superadmin') || !auth()->user()->hasPermissionTo('war approve'), function($query) use($ids) {
                $query->whereIn('id', $ids)
                    ->orWhere('id', auth()->user()->id);
            })
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('firstname', 'LIKE', '%'.$search.'%')
                        ->orWhere('lastname', 'LIKE', '%'.$search.'%');
                });
            })
            ->paginate($settings->data_per_page)
            ->appends(request()->query());

        return view('war.index')->with([
            'search' => $search,
            'users' => $users
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
            'accounts_visited' => $request->accounts_visited,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'week_number' => $request->week,
            'date_submitted' => $date_submitted,
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

                    // check if there's attachment
                    if($request->hasFile("branch_attachment.$date.$branch_id")) {
                        // save attachment
                        $files = $request->file("branch_attachment.$date.$branch_id");

                        foreach($files as $file) {
                            $title = $file->getClientOriginalName();
                            $filename = time().'_'.$title;

                            $file->storeAs('uploads/war_branch_attachments/'.$war->id, $filename, 'public');

                            $attachment = new WeeklyActivityReportAttachment([
                                'weekly_activity_report_branch_id' => $war_branch->id,
                                'title' => $title,
                                'file' => 'uploads/war_branch_attachments/'.$filename
                            ]);
                            $attachment->save();
                        }

                    }
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

            // approval
            $approval = new WeeklyActivityReportApproval([
                'user_id' => auth()->user()->id,
                'weekly_activity_report_id' => $war->id,
                'status' => $request->status,
                'remarks' => '',
            ]);
            $approval->save();
        }

        // logs
        activity('create')
            ->performedOn($war)
            ->log(':causer.firstname :causer.lastname has created weekly productivity report  period covered: :subject.date_from to :subject.date_to');

        return redirect()->route('war.list', $war->user_id)->with([
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

        $supervisor_id = $weekly_activity_report->user->getImmediateSuperiorId();

        $area_status_arr = [
            'VISITED' => 'success',
            'NOT VISITED' => 'danger',
            'DEVIATION' => 'warning',
        ];

        if(auth()->user()->id != 1) {// if not superadmin
            // logs
            activity('viewed')
                ->performedOn($weekly_activity_report)
                ->log(':causer.firstname :causer.lastname has viewed weekly productivity report of [ :subject.user.firstname :subject.user.lastname ] period covered: :subject.date_from to :subject.date_to');
        }

        return view('war.show')->with([
            'weekly_activity_report' => $weekly_activity_report,
            'status_arr' => $this->status_arr,
            'supervisor_id' => $supervisor_id,
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

        $changes_arr['old'] = $weekly_activity_report->getOriginal();

        $date_submitted = NULL;
        if($request->status == 'submitted') {
            $date_submitted = date('Y-m-d');
        }

        $weekly_activity_report->update([
            'accounts_visited' => $request->accounts_visited,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'week_number' => $request->week,
            'date_submitted' => $date_submitted,
            'highlights' => $request->highlights,
            'objectives' => $request->objective,
            'status' => $request->status
        ]);

        $changes_arr['changes'] = $weekly_activity_report->getChanges();

        // areas
        foreach($weekly_activity_report->areas as $area) {
            $war_branches = $area->war_branches;
            foreach($war_branches as $war_branch) {
                $war_branch->delete();
            }
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

                    // check if there's attachment
                    if($request->hasFile("branch_attachment.$date.$branch_id")) {
                        // save attachment
                        $files = $request->file("branch_attachment.$date.$branch_id");

                        foreach($files as $file) {
                            $title = $file->getClientOriginalName();
                            $filename = time().'_'.$title;

                            $file->storeAs('uploads/war_branch_attachments/'.$weekly_activity_report->id, $filename, 'public');

                            $attachment = new WeeklyActivityReportAttachment([
                                'weekly_activity_report_branch_id' => $war_branch->id,
                                'title' => $title,
                                'file' => 'uploads/war_branch_attachments/'.$filename
                            ]);
                            $attachment->save();
                        }

                        // delete the previous attachments
                        if(!empty($request->branch_attachment_exists[$date][$branch_id])) {
                            foreach($request->branch_attachment_exists[$date][$branch_id] as $attachment_id) {
                                $prev_attachment = WeeklyActivityReportAttachment::find($attachment_id);
                                if(Storage::exists($prev_attachment->file)) {
                                    Storage::delete($prev_attachment->file);
                                    $prev_attachment->delete();
                                }
                            }
                        }

                    } else {
                        if(!empty($request->branch_attachment_exists[$date][$branch_id])) {
                            foreach($request->branch_attachment_exists[$date][$branch_id] as $attachment_id) {
                                $attachment = WeeklyActivityReportAttachment::find($attachment_id);
                                if(!empty($attachment)) {
                                    $attachment->update([
                                        'weekly_activity_report_branch_id' => $war_branch->id
                                    ]);
                                }
                            }
                        }
                    }
                    
                }
            }
        }

        activity('update')
            ->performedOn($weekly_activity_report)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated weekly productivity report period covered :subject.date_from to :subject.date_to .');

        if($request->status == 'submitted') {

            $supervisor_id = $weekly_activity_report->user->getImmediateSuperiorId();
            if(!empty($supervisor_id)) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new WeeklyActivityReportSubmitted($weekly_activity_report));
                }
            }

            // approval
            $approval = new WeeklyActivityReportApproval([
                'user_id' => auth()->user()->id,
                'weekly_activity_report_id' => $weekly_activity_report->id,
                'status' => $request->status,
                'remarks' => '',
            ]);
            $approval->save();

            return redirect()->route('war.list', $weekly_activity_report->user_id)->with([
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

        $area_status_arr = [
            'VISITED' => 'success',
            'NOT VISITED' => 'danger',
            'DEVIATION' => 'warning',
        ];

        $pdf = PDF::loadView('war.pdf', [
            'weekly_activity_report' => $weekly_activity_report,
            'area_status_arr' => $area_status_arr
        ]);

        if(auth()->user()->id !== 1) {
            // logs
            activity('printed')
                ->performedOn($weekly_activity_report)
                ->log(':causer.firstname :causer.lastname has printed weekly productivity report period covered: :subject.date_from to :subject.date_to');
        }

        return $pdf->stream('weekly-activity-report-'.$weekly_activity_report->date.'-'.time().'.pdf');
    }

    public function approval(Request $request, $id) {
        $request->validate([
            'status' => 'required',
            'remarks' => 'max:2000|required'
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

        // logs
        activity('approval')
            ->performedOn($war)
            ->log(':causer.firstname :causer.lastname has '.$request->status.' weekly productivity report period covered: :subject.date_from to :subject.date_to');

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
