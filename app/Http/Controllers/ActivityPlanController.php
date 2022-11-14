<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanApproval;
use App\Models\OrganizationStructure;
use App\Http\Requests\StoreActivityPlanRequest;
use App\Http\Requests\UpdateActivityPlanRequest;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivityPlanSubmitted;

use App\Http\Traits\GlobalTrait;

class ActivityPlanController extends Controller
{
    use GlobalTrait;

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

        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin')) {
            $activity_plans = ActivityPlan::ActivityPlanSearch($search, $settings->data_per_page);
        } else { // restricted to self and supervisors
            // get user subordinates
            $subordinate_ids = $this->getSubordinates(auth()->user()->id);

            $activity_plans = ActivityPlan::ActivityPlanSearchRestricted($search, $settings->data_per_page, $subordinate_ids);
        }

        return view('mcp.index')->with([
            'search' => $search,
            'activity_plans' => $activity_plans,
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
        $position = [];
        $organizations = auth()->user()->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        return view('mcp.create')->with([
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
            
            foreach($activity_plan_data as $year => $months) {
                foreach($months as $month => $data) {
                    // check objectives
                    if(!empty($data['objectives'])) {

                        $activity_plan = new ActivityPlan([
                            'user_id' => auth()->user()->id,
                            'month' => $data['month'],
                            'year' => $data['year'],
                            'objectives' => $data['objectives'],
                            'status' => $request->status
                        ]);
                        $activity_plan->save();
    
                        // details
                        foreach($data['details'] as $date => $details) {
                            // dates
                            foreach($details['lines'] as $val) {
                                $activity_plan_detail = new ActivityPlanDetail([
                                    'activity_plan_id' => $activity_plan->id,
                                    'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                    'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                    'day' => $details['day'],
                                    'date' => $date,
                                    'exact_location' => $val['location'],
                                    'activity' => $val['purpose']
                                ]);
                                $activity_plan_detail->save();
                            }
                        }

                    } else {
                        throw ValidationException::withMessages(['objectives' => 'Objectives is required']);
                    }

                }
            }

            // notifications
            if($request->status == 'submitted') {
                $users = [];
                $organizations = $activity_plan->user->organizations;
                if(!empty($organizations)) {
                    foreach($organizations as $organization) {
                        if(!empty($organization->reports_to_id)) {
                            $reports_to = OrganizationStructure::find($organization->reports_to_id);
                            if(isset($reports_to->user)) {
                                $users[] = $reports_to->user;
                            } else { // check upper level suppervisor
                                if(!empty($reports_to->reports_to_id)) {
                                    $reports_to2 = OrganizationStructure::find($reports_to->reports_to_id);
                                    if(isset($reports_to2->user)) {
                                        $users[] = $reports_to2->user;
                                    }
                                }
                            }
                        }
                    }
                }

                if(!empty($users)) {
                    foreach($users as $user) {
                        Notification::send($user, new ActivityPlanSubmitted($activity_plan));
                    }
                }
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
        $subordinate_ids = $this->getSubordinates(auth()->user()->id);

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
        $activity_plan_data[$activity_plan->year][$activity_plan->month] = [
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

            $details[$detail->date]['day'] = $detail->day;
            $details[$detail->date]['date'] = date('M. d', strtotime($detail->date));
            $details[$detail->date]['class'] = $class;
            $details[$detail->date]['lines'][] = [
                'id' => $detail->id,
                'location' => $detail->exact_location,
                'branch_id' => $detail->branch_id,
                'branch_name' => isset($detail->branch) ? '['.$detail->branch->branch_code.'] '.$detail->branch->branch_name : '',
                'purpose' => $detail->activity,
                'user_id' => $detail->user_id
            ];
        }

        $activity_plan_data[$activity_plan->year][$activity_plan->month]['details'] = $details;

        Session::put('activity_plan_data', $activity_plan_data);

        $position = [];
        $organizations = $activity_plan->user->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        return view('mcp.edit')->with([
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

            $data = $activity_plan_data[$activity_plan->year][$activity_plan->month];

            if(!empty($data['objectives'])) {
            
                $activity_plan->update([
                    'year' => $data['year'],
                    'month' => $data['month'],
                    'objectives' => $data['objectives'],
                    'status' => $request->status
                ]);

                // details
                foreach($data['details'] as $date => $detail) {
                    foreach($detail['lines'] as $val) {
                        // check if already exist
                        if(isset($val['id'])) { // update
                            $activity_plan_detail = ActivityPlanDetail::find($val['id']);
                            if(!empty($activity_plan_detail)) {
                                $activity_plan_detail->update([
                                    'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                    'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                    'day' => $detail['day'],
                                    'date' => $date,
                                    'exact_location' => $val['location'],
                                    'activity' => $val['purpose']
                                ]);
                            } else {
                                $activity_plan_detail = new ActivityPlanDetail([
                                    'activity_plan_id' => $activity_plan->id,
                                    'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                    'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                    'day' => $detail['day'],
                                    'date' => $date,
                                    'exact_location' => $val['location'],
                                    'activity' => $val['purpose']
                                ]);
                                $activity_plan_detail->save();
                            }
                        } else { // insert
                            $activity_plan_detail = new ActivityPlanDetail([
                                'activity_plan_id' => $activity_plan->id,
                                'user_id' => empty($val['user_id']) ? NULL : $val['user_id'],
                                'branch_id' => empty($val['branch_id']) ? NULL : $val['branch_id'],
                                'day' => $detail['day'],
                                'date' => $date,
                                'exact_location' => $val['location'],
                                'activity' => $val['purpose']
                            ]);
                            $activity_plan_detail->save();
                        }
                    }
                }

            } else {
                throw ValidationException::withMessages(['objectives' => 'Objectives is required']);
            }

            if($request->status == 'draft') {
                return back()->with([
                    'message_success' => 'MCP has been saved.'
                ]);
            } else {

                // notifications
                if($request->status == 'submitted') {
                    $users = [];
                    $organizations = $activity_plan->user->organizations;
                    if(!empty($organizations)) {
                        foreach($organizations as $organization) {
                            if(!empty($organization->reports_to_id)) {
                                $reports_to = OrganizationStructure::find($organization->reports_to_id);
                                if(isset($reports_to->user)) {
                                    $users[] = $reports_to->user;
                                }
                            }
                        }
                    }

                    if(!empty($users)) {
                        foreach($users as $user) {
                            Notification::send($user, new ActivityPlanSubmitted($activity_plan));
                        }
                    }
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

    public function getSubordinates($user_id) {
        $user = User::findOrFail($user_id);
        $organizations = $user->organizations;
        $subordinate_ids = [];
        foreach($organizations as $organization) {
            $subordinates = OrganizationStructure::where('reports_to_id', $organization->id)
            ->get();
            foreach($subordinates as $subordinate) {
                if(!empty($subordinate->user_id)) {
                    $subordinate_ids[] = $subordinate->user_id;
                }
                // get second level subordinates
                $subordinates2 = OrganizationStructure::where('reports_to_id', $subordinate->id)
                ->get();
                foreach($subordinates2 as $subordinate2) {
                    if(!empty($subordinate2->user_id)) {
                        $subordinate_ids[] = $subordinate2->user_id;
                    }
                    // get third level subordinates
                    $subordinates3 = OrganizationStructure::where('reports_to_id', $subordinate2->id)
                    ->get();
                    foreach($subordinates3 as $subordinate3) {
                        if(!empty($subordinate3->user_id)) {
                            $subordinate_ids[] = $subordinate3->user_id;
                        }
                    }
                }
            }
        }

        return $subordinate_ids;
    }
}
