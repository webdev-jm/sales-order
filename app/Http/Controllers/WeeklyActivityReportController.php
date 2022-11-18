<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OrganizationStructure;
use App\Models\Area;
use App\Models\WeeklyActivityReport;
use App\Models\WeeklyActivityReportActionPlan;
use App\Models\WeeklyActivityReportActivity;
use App\Models\WeeklyActivityReportArea;
use App\Models\WeeklyActivityReportCollection;
use App\Models\WeeklyActivityReportObjective;
use App\Http\Requests\StoreWeeklyActivityReportRequest;
use App\Http\Requests\UpdateWeeklyActivityReportRequest;

use Illuminate\Http\Request;

use App\Http\Traits\GlobalTrait;

class WeeklyActivityReportController extends Controller
{

    use GlobalTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $settings = $this->getSettings();

        $search = trim($request->get('search'));

        $subordinate_ids = $this->getSubordinates(auth()->user()->id);

        $weekly_activity_reports = WeeklyActivityReport::WeeklyActivityReportSearch($search, $settings->data_per_page, $subordinate_ids);

        return view('war.index')->with([
            'search' => $search,
            'weekly_activity_reports' => $weekly_activity_reports
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $areas = Area::orderBy('area_code', 'ASC')
        ->get();

        $areas_arr = [
            '' => ''
        ];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }

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
        $war = new WeeklyActivityReport([
            'user_id' => auth()->user()->id,
            'area_id' => $request->area_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'week_number' => $request->week,
            'date_submitted' => NULL,
            'highlights' => $request->highlights,
            'status' => 'draft',
        ]);
        $war->save();

        // objectives
        $objective = new WeeklyActivityReportObjective([
            'weekly_activity_report_id' => $war->id,
            'objective' => $request->objective
        ]);
        $objective->save();

        // areas
        foreach($request->area_date as $key => $date) {
            $area = new WeeklyActivityReportArea([
                'weekly_activity_report_id' => $war->id,
                'date' => $date,
                'day' => $request->area_day[$key],
                'location' => $request->area_covered[$key],
                'in_base' => $request->area_in_base[$key],
                'remarks' => $request->area_remarks[$key]
            ]);
            $area->save();
        }

        // collections
        $collection = new WeeklyActivityReportCollection([
            'weekly_activity_report_id' => $war->id,
            'beginning_ar' => $request->beginning_ar,
            'due_for_collection' => $request->due_for_collection,
            'beginning_hanging_balance' => $request->beginning_hanging_balance,
            'target_reconciliations' => $request->target_reconciliations,
            'week_to_date' => $request->week_to_date,
            'month_to_date' => $request->month_to_date,
            'month_target' => $request->month_target,
            'balance_to_sell' => $request->balance_to_sell,
        ]);
        $collection->save();

        // action plans
        foreach($request->action_plan as $key => $plan) {
            $action_plan = new WeeklyActivityReportActionPlan([
                'weekly_activity_report_id' => $war->id,
                'action_plan' => $plan,
                'time_table' => $request->time_table[$key],
                'person_responsible' => $request->person_responsible[$key],
            ]);
            $action_plan->save();
        }

        // ativities
        foreach($request->activity as $key => $activity) {
            $war_activity = new WeeklyActivityReportActivity([
                'weekly_activity_report_id' => $war->id,
                'activity' => $activity,
                'no_of_days_weekly' => $request->no_of_days_weekly[$key],
                'no_of_days_mtd' => $request->no_of_days_mtd[$key],
                'no_of_days_ytd' => $request->no_of_days_ytd[$key],
                'remarks' => $request->activity_remarks[$key],
                'percent_to_total_working_days' => $request->total_working_days[$key]
            ]);
            $war_activity->save();
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
    public function show(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function edit(WeeklyActivityReport $weeklyActivityReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateWeeklyActivityReportRequest  $request
     * @param  \App\Models\WeeklyActivityReport  $weeklyActivityReport
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWeeklyActivityReportRequest $request, WeeklyActivityReport $weeklyActivityReport)
    {
        //
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
                        // get fourth level subordinates
                        $subordinates4 = OrganizationStructure::where('reports_to_id', $subordinate3->id)
                        ->get();
                        foreach($subordinates4 as $subordinate4) {
                            if(!empty($subordinate4->user_id)) {
                                $subordinate_ids[] = $subordinate4->user_id;
                            }
                        }
                    }
                }
            }
        }

        // return and remove duplicates
        return array_unique($subordinate_ids);
    }
}
