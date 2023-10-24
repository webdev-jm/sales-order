<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Branch;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanApproval;
use App\Models\ActivityPlanDetailTrip;
use App\Models\OrganizationStructure;
use App\Http\Requests\StoreActivityPlanRequest;
use App\Http\Requests\UpdateActivityPlanRequest;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivityPlanSubmitted;

use App\Http\Traits\GlobalTrait;
use App\Http\Traits\MonthDeadline;
use App\Http\Traits\ReminderTrait;

use Barryvdh\DomPDF\Facade\Pdf;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ActivityPlanImport;

class ActivityPlanController extends Controller
{
    use GlobalTrait;
    use MonthDeadline;
    use ReminderTrait;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'rejected' => 'danger',
        'approved' => 'success'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // clear session data
        Session::forget('activity_plan_data');

        $search = trim($request->get('search'));

        $settings = $this->getSettings();

        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales')) {
            $activity_plans = ActivityPlan::ActivityPlanSearch($search, $settings->data_per_page);
        } else { // restricted to self and supervisors
            // get user subordinates
            $subordinate_ids = [];
            $ids = auth()->user()->getSubordinateIds();
            foreach($ids as $level => $id_arr) {
                foreach($id_arr as $id) {
                    $subordinate_ids[] = $id;
                }
            }

            $activity_plans = ActivityPlan::ActivityPlanSearchRestricted($search, $settings->data_per_page, $subordinate_ids);
        }

        // deadline countdown
        $year = date('Y');
        $month = date('m');
        $deadline = $this->getMonthDeadline($year, $month);
        $days_left = $this->getDeadlineCount($deadline);
        
        // set next month data
        $next_month = $month;
        if($month == 12) {
            $next_month = 1;
            $year = $year + 1;
        } else {
            $next_month = $month + 1;
        }

        return view('mcp.index')->with([
            'search' => $search,
            'activity_plans' => $activity_plans,
            'status_arr' => $this->status_arr,
            'days_left' => $days_left,
            'deadline' => $deadline,
            'year' => $year,
            'next_month' => $next_month
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $position = [];
        $organizations = auth()->user()->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        return view('mcp.create2')->with([
            'position' => $position
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreActivityPlanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActivityPlanRequest $request)
    {
        $activity_plan_data = Session::get('activity_plan_data');

        if(!empty($activity_plan_data)) {
            
            foreach($activity_plan_data as $year => $data) {

                // check objectives
                if(!empty($data['objectives'])) {

                    // details
                    if(!empty($data['details'][$data['month']])) {
                        // validate lines
                        $line_error = 0;
                        $line_empty = 1;
                        foreach($data['details'][$data['month']] as $date => $details) {
                            // dates
                            foreach($details['lines'] as $val) {
                                // check for error
                                if(empty($val['branch_id']) 
                                    && 
                                    (!empty($val['user_id']) || 
                                    !empty($val['location']) ||
                                    !empty(trim($val['purpose'])) ||
                                    !empty($val['account_id']))
                                ) {
                                    $line_error = 1;
                                }

                                // check if all lines are empty
                                if(!empty($val['branch_id']) || !empty($val['user_id']) || !empty($val['location']) || !empty(trim($val['purpose'])) || !empty($val['account_id'])) {
                                    $line_empty = 0;
                                }
                            }
                        }

                        if($line_error == 0 && $line_empty == 0) {

                            $activity_plan = new ActivityPlan([
                                'user_id' => auth()->user()->id,
                                'month' => $data['month'],
                                'year' => $data['year'],
                                'objectives' => $data['objectives'],
                                'status' => $request->status
                            ]);
                            $activity_plan->save();

                            // logs
                            activity('create')
                            ->performedOn($activity_plan)
                            ->log(':causer.firstname :causer.lastname has created activity plan for :subject.year :subject.month');

                            foreach($data['details'][$data['month']] as $date => $details) {
                                foreach($details['lines'] as $val) {
                                    $activity_plan_detail = new ActivityPlanDetail([
                                        'activity_plan_id' => $activity_plan->id,
                                        'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                        'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                        'day' => $details['day'],
                                        'date' => $date,
                                        'exact_location' => $val['location'],
                                        'activity' => $val['purpose'],
                                        'work_with' => $val['work_with'] ?? NULL,
                                    ]);
                                    $activity_plan_detail->save();

                                    // check there's trip data
                                    if(isset($val['trip']) && !empty($val['trip'])) {
                                        $trip_data = $val['trip'];
                                        $activity_plan_detail_trip = new ActivityPlanDetailTrip([
                                            'activity_plan_detail_id' => $activity_plan_detail->id,
                                            'trip_number' => $trip_data['trip_number'],
                                            'departure' => $trip_data['departure'],
                                            'arrival' => $trip_data['arrival'],
                                            'reference_number' => $trip_data['reference_number']
                                        ]);
                                        $activity_plan_detail_trip->save();
                                    }
                                    
                                }
                            }
                        } else {
                            return back()->with([
                                'message_error' => 'Please complete branch details.'
                            ]);
                        }

                    } else {
                        return back()->with([
                            'message_error' => 'Please fill up activity plan details.'
                        ]);
                    }
                    
                } else {
                    throw ValidationException::withMessages(['objectives' => 'Objectives is required']);
                }
            }

            
            if($request->status == 'submitted') {

                // notifications
                // $users = [];
                // $supervisor_ids = $activity_plan->user->getSupervisorIds();
                // foreach($supervisor_ids as $id) {
                //     $user = User::find($id);
                //     if(!empty($user)) {
                //         $users[] = $user;
                //     }
                // }

                $users = [];
                $supervisor_ids = [];
                $supervisor_id = $activity_plan->user->getImmediateSuperiorId();
                if(!empty($supervisor_id)) {
                    $user = User::find($supervisor_id);
                    if(!empty($user)) {
                        $users[] = $user;
                    }
                }

                $supervisor_ids[] = $supervisor_id;

                if(!empty($users)) {
                    foreach($users as $user) {
                        Notification::send($user, new ActivityPlanSubmitted($activity_plan));
                    }
                }

                // approvals
                $approval = new ActivityPlanApproval([
                    'user_id' => auth()->user()->id,
                    'activity_plan_id' => $activity_plan->id,
                    'status' => 'submitted',
                    'remarks' => null
                ]);
                $approval->save();

                // create reminder
                $this->setReminder('ActivityPlan', $activity_plan->id, 'The activity plan was submitted for your approval', $supervisor_ids, 'mcp/'.$activity_plan->id);

            }
            
            return redirect()->route('mcp.index')->with([
                'message_success' => 'MCP has been saved.'
            ]);
        } else {
            return back()->with([
                'message_error' => 'Please fill up the form before saving.'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $activity_plan = ActivityPlan::findOrFail($id);

        $schedule_data = [];
        foreach($activity_plan->details as $detail) {
            if(isset($detail->branch->branch_name)) {
                $schedule_data[] = [
                    'title' => '['.strtoupper($detail->branch->branch_name).'] '.(!empty($detail->activity) ? '- '.$detail->activity : ''),
                    'start' => $detail->date,
                    'allDay' => true,
                    'backgroundColor' => '#09599e',
                    'borderColor' => '#024d4d',
                    'id' => $detail->id
                ];
            } else if(!empty($detail->activity)) {
                $schedule_data[] = [
                    'title' => $detail->activity,
                    'start' => $detail->date,
                    'allDay' => true,
                    'backgroundColor' => '#09599e',
                    'borderColor' => '#024d4d',
                    'id' => $detail->id
                ];
            }
        }

        $position = [];
        $organizations = $activity_plan->user->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        // get user subordinates
        $subordinate_ids = [];
        $ids = auth()->user()->getSubordinateIds();
        foreach($ids as $level => $id_arr) {
            foreach($id_arr as $id) {
                $subordinate_ids[] = $id;
            }
        }

        return view('mcp.show')->with([
            'position' => $position,
            'activity_plan' => $activity_plan,
            'schedule_data' => $schedule_data,
            'status_arr' => $this->status_arr,
            'subordinate_ids' => $subordinate_ids
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $activity_plan = ActivityPlan::findOrFail($id);

        // header
        $activity_plan_data[$activity_plan->year] = [
            'year' => $activity_plan->year,
            'month' => $activity_plan->month,
            'objectives' => $activity_plan->objectives,
        ];

        // details
        $details = [];
        foreach($activity_plan->details as $detail) {
            $class = '';
            if($detail->day == 'Sun') {
                $class = 'bg-navy';
            } else if($detail->day == 'Sat') {
                $class = 'bg-secondary';
            }

            $account = $detail->branch->account ?? '';
            $account_name = '';
            if(!empty($account)) {
                $account_name = '['.$account->account_code.'], '.$account->short_name;
            }

            $details[$detail->date]['day'] = $detail->day;
            $details[$detail->date]['date'] = date('M. d', strtotime($detail->date));
            $details[$detail->date]['class'] = $class;
            $details[$detail->date]['lines'][] = [
                'id' => $detail->id,
                'location' => $detail->exact_location,
                'account_id' => $detail->branch->account_id ?? '',
                'account_name' => $account_name,
                'branch_id' => $detail->branch_id,
                'branch_name' => isset($detail->branch) ? '['.$detail->branch->branch_code.'] '.$detail->branch->branch_name : '',
                'purpose' => $detail->activity,
                'user_id' => $detail->user_id,
                'work_with' => $detail->work_with,
            ];
        }

        $activity_plan_data[$activity_plan->year]['details'][$activity_plan->month] = $details;

        if(empty(Session::get('activity_plan_data'))) {
            Session::put('activity_plan_data', $activity_plan_data);
        }

        $position = [];
        $organizations = $activity_plan->user->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        return view('mcp.edit2')->with([
            'position' => $position,
            'activity_plan' => $activity_plan,
            'status_arr' => $this->status_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateActivityPlanRequest  $request
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActivityPlanRequest $request, $id)
    {
        $activity_plan_data = Session::get('activity_plan_data');
        $activity_plan = ActivityPlan::findOrFail($id);

        if(!empty($activity_plan_data)) {

            foreach($activity_plan_data as $year => $data) {

                // check objectives
                if(!empty($data['objectives'])) {

                    // details
                    if(!empty($data['details'][$data['month']])) {
                        // validate lines
                        $line_error = 0;
                        $line_empty = 1;
                        foreach($data['details'][$data['month']] as $date => $details) {
                            // dates
                            foreach($details['lines'] as $val) {
                                // check for error
                                if(empty($val['branch_id']) 
                                    && 
                                    (!empty($val['user_id']) || 
                                    !empty($val['location']) ||
                                    !empty($val['purpose']) ||
                                    !empty($val['account_id']))
                                ) {
                                    $line_error = 1;
                                }

                                // check if all lines are empty
                                if(!empty($val['branch_id']) || !empty($val['user_id']) || !empty($val['location']) || !empty($val['purpose']) || !empty($val['account_id'])) {
                                    $line_empty = 0;
                                }
                            }
                        }

                        if($line_error == 0 && $line_empty == 0) {

                            $changes_arr['old'] = $activity_plan->getOriginal();
                            // update
                            $activity_plan->update([
                                'year' => $data['year'],
                                'month' => $data['month'],
                                'objectives' => $data['objectives'],
                                'status' => $request->status
                            ]);

                            $changes_arr['changes'] = $activity_plan->getChanges();

                            // logs
                            activity('update')
                            ->performedOn($activity_plan)
                            ->withProperties($changes_arr)
                            ->log(':causer.firstname :causer.lastname has updated activity plan :subject.year :subject.month');

                            foreach($data['details'][$data['month']] as $date => $details) {
                                foreach($details['lines'] as $val) {
                                    // check if already exist
                                    if(isset($val['id'])) { // update
                                        $activity_plan_detail = ActivityPlanDetail::find($val['id']);
                                        if(!empty($activity_plan_detail)) {
                                            $activity_plan_detail->update([
                                                'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                                'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                                'day' => $details['day'],
                                                'date' => $date,
                                                'exact_location' => $val['location'],
                                                'activity' => $val['purpose'],
                                                'work_with' => $val['work_with'] ?? NULL,
                                            ]);
                                        } else {
                                            $activity_plan_detail = new ActivityPlanDetail([
                                                'activity_plan_id' => $activity_plan->id,
                                                'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                                'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                                'day' => $details['day'],
                                                'date' => $date,
                                                'exact_location' => $val['location'],
                                                'activity' => $val['purpose'],
                                                'work_with' => $val['work_with'] ?? NULL,
                                            ]);
                                            $activity_plan_detail->save();
                                        }
                                    } else { // insert
                                        $activity_plan_detail = new ActivityPlanDetail([
                                            'activity_plan_id' => $activity_plan->id,
                                            'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                            'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                            'day' => $details['day'],
                                            'date' => $date,
                                            'exact_location' => $val['location'],
                                            'activity' => $val['purpose'],
                                            'work_with' => $val['work_with'] ?? NULL,
                                        ]);
                                        $activity_plan_detail->save();
                                    }
                                }
                            }
                        } else {
                            return back()->with([
                                'message_error' => 'Please complete branch details.'
                            ]);
                        }

                    } else {
                        return back()->with([
                            'message_error' => 'Please fill up activity plan details.'
                        ]);
                    }
                    
                } else {
                    throw ValidationException::withMessages(['objectives' => 'Objectives is required']);
                }
            }

            if($request->status == 'draft') {
                return back()->with([
                    'message_success' => 'MCP has been saved.'
                ]);
            } else {

                // notifications
                if($request->status == 'submitted') {
                    // notifications
                    // $users = [];
                    // $supervisor_ids = $activity_plan->user->getSupervisorIds();
                    // foreach($supervisor_ids as $id) {
                    //     $user = User::find($id);
                    //     if(!empty($user)) {
                    //         $users[] = $user;
                    //     }
                    // }

                    $users = [];
                    $supervisor_ids = [];
                    $supervisor_id = $activity_plan->user->getImmediateSuperiorId();
                    if(!empty($supervisor_id)) {
                        $user = User::find($supervisor_id);
                        if(!empty($user)) {
                            $users[] = $user;
                        }
                        
                        $supervisor_ids[] = $supervisor_id;
                    }

                    
                    if(!empty($users)) {
                        foreach($users as $user) {
                            Notification::send($user, new ActivityPlanSubmitted($activity_plan));
                        }
                    }

                    // create reminder
                    $this->setReminder('ActivityPlan', $activity_plan->id, 'The activity plan was submitted for your approval', $supervisor_ids, 'mcp/'.$activity_plan->id);

                    // approvals
                    $approval = new ActivityPlanApproval([
                        'user_id' => auth()->user()->id,
                        'activity_plan_id' => $activity_plan->id,
                        'status' => 'submitted',
                        'remarks' => null
                    ]);
                    $approval->save();
                }

                return redirect()->route('mcp.index')->with([
                    'message_success' => 'MCP has been saved.'
                ]);
            }

        } else {
            return back()->with([
                'message_error' => 'Please fill up the form before saving.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActivityPlan  $activityPlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActivityPlan $activityPlan)
    {
        //
    }

    public function printPDF($id) {
        $activity_plan = ActivityPlan::findOrFail($id);

        $position = [];
        $organizations = $activity_plan->user->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        $last_day = date('t', strtotime($activity_plan->year.'-'.$activity_plan->month.'-01'));
        $lines = [];
        for($i = 1; $i <= (int)$last_day; $i++) {
            $date = $activity_plan->year.'-'.$activity_plan->month.'-'.($i < 10 ? '0'.$i : $i);
            $day = date('D', strtotime($date));
            $class = '';
            if($day == 'Sun') {
                $class = 'bg-navy';
            } else if($day == 'Sat') {
                $class = 'bg-secondary';
            }

            // check details
            $details = $activity_plan->details()->where('date', $date)
            ->get();
            $data = [];
            if(!empty($details)) {
                foreach($details as $detail) {
                    $branch_name = '';
                    $account_name = '';
                    if(!empty($detail->branch_id)) {
                        $branch_name = $detail->branch->branch_code.' - '.$detail->branch->branch_name;
                        $account_name = $detail->branch->account->short_name;
                    }

                    $data[] = [
                        'location' => $detail->exact_location,
                        'account_name' => $account_name,
                        'branch_name' => $branch_name,
                        'purpose' => $detail->activity,
                        'work_with' => !empty($detail->user_id) ? $detail->user->fullName() : $detail->work_with,
                    ];
                }
            } else {
                $data[] = [
                    'location' => '',
                    'account_name' => '',
                    'branch_name' => '',
                    'purpose' => '',
                    'work_with' => ''
                ];
            }

            $lines[$date] = [
                'day' => $day,
                'class' => $class,
                'lines' => $data
            ];
        }

        $pdf = PDF::loadView('mcp.pdf', [
            'activity_plan' => $activity_plan,
            'position' => $position,
            'lines' => $lines
        ]);

        return $pdf->stream('weekly-activity-report-'.$activity_plan->year.'-'.$activity_plan->month.'-'.time().'.pdf');
    }

    public function upload(Request $request) {
        $request->validate([
            'upload_file' => [
                'mimes:xlsx,xls',
                'required'
            ]
        ]);

        $week_days_arr = [
            '0' => 'Sun',
            '1' => 'Mon',
            '2' => 'Tue',
            '3' => 'Wed',
            '4' => 'Thu',
            '5' => 'Fri',
            '6' => 'Sat'
        ];

        $year = date('Y');
        $month = date('m');
        $objectives = '';
        $data = [];

        $path1 = $request->file('upload_file')->store('temp');
        $path = storage_path('app').'/'.$path1;
        $imports = Excel::toArray(new ActivityPlanImport, $path);
        $row_num = 0;
        foreach($imports[0] as $row) {
            $row_num++;
            
            // YEAR
            if($row_num == 1 && $row[0] == 'YEAR') {
                $year = $row[1];
            }
            // MONTH
            if($row_num == 2 && $row[0] == 'MONTH') {
                $month = $row[1] < 10 ? '0'.$row[1] : $row[1];
            }
            // OBJECTIVES
            if($row_num == 3 && $row[0] == 'OBJECTIVES') {
                $objectives = $row[1];
            }

            if($row_num > 4) {
                if(!empty($row[0])) {
                    $date_key = $year.'-'.$month.'-'.($row[0] < 10 ? '0'.$row[0] : $row[0]);
    
                    $data[$date_key][] = [
                        'account_code' => trim($row[2] ?? ''),
                        'branch_code' => trim($row[3]),
                        'location' => trim($row[4] ?? ''),
                        'purpose' => trim($row[5] ?? ''),
                        'work_with' => trim($row[6] ?? ''),
                    ];
                }
            }
        }

        // render data to session
        $activity_plan_detail[$year] = [
            'year' => $year,
            'month' => $month,
            'objectives' => $objectives,
        ];
        
        $details = [];
        foreach($data as $date => $lines) {
            $day = $week_days_arr[date('w', strtotime($date))];
            $date_name = date('F. d', strtotime($date));

            $class = '';
            if($day == 'Sat') {
                $class = 'bg-secondary';
            }
            if($day == 'Sun') {
                $class = 'bg-navy';
            }

            $details[$month][$date] = [
                'day' => $day,
                'date' => $date_name,
                'class' => $class,
            ];

            foreach($lines as $line) {
                // branch
                $branch = Branch::where('branch_code', $line['branch_code'])
                ->where('branch_code', '<>', '')
                ->first();
                // account
                $account = Account::where('account_code', $line['account_code'])->first();
                if(empty($account)) {
                    $account = $branch->account ?? null;
                }
                // user
                $user = User::where('email', $line['work_with'])->first();

                $details[$month][$date]['lines'][] = [
                    'location' => $line['location'] ?? '',
                    'account_id' => $account->id ?? '',
                    'account_name' => $account->short_name ?? '',
                    'branch_id' => $branch->id ?? '',
                    'branch_name' => $branch->branch_name ?? '',
                    'purpose' => $line['purpose'],
                    'user_id' => $user->id ?? '',
                    'work_with' => $line['work_with'],
                ];
            }

        }

        $activity_plan_detail[$year]['details'] = $details;

        Session::put('activity_plan_data', $activity_plan_detail);

        return back()->with([
            'message_success' => 'Data was uploaded.'
        ]);

    }
}
