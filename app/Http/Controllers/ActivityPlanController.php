<?php

namespace App\Http\Controllers;

use App\Models\ActivityPlan;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanApproval;
use App\Http\Requests\StoreActivityPlanRequest;
use App\Http\Requests\UpdateActivityPlanRequest;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class ActivityPlanController extends Controller
{
    use GlobalTrait;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
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

        $activity_plans = ActivityPlan::ActivityPlanSearch($search, $settings->data_per_page);

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

        $position = [];
        $organizations = $activity_plan->user->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $position[] = $organization->job_title->job_title;
            }
        }

        return view('mcp.show')->with([
            'position' => $position,
            'activity_plan' => $activity_plan
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

            $details[$detail->date] = [
                'day' => $detail->day,
                'date' => date('M. d', strtotime($detail->date)),
                'class' => $class,
            ];
            $details[$detail->date]['lines'][] = [
                'id' => $detail->id,
                'location' => $detail->exact_location,
                'branch_id' => $detail->branch_id,
                'branch_name' => $detail->branch->branch_name ?? '',
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

            if($request->status == 'draft') {
                return back()->with([
                    'message_success' => 'MCP has been saved.'
                ]);
            } else {
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
}
