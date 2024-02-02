<?php

namespace App\Http\Controllers;

use App\Http\Traits\GlobalTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\UserBranchSchedule;
use App\Models\Department;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripApproved;
use App\Notifications\TripRejected;

class TripController extends Controller
{
    use GlobalTrait;

    public $setting;
    public $status_arr = [
        'submitted'             => 'secondary',
        'for revision'          => 'warning',
        'approved'              => 'primary',
        'returned'              => 'danger',
        'for approval'          => 'info',
        'approved by finance'   => 'success',
        'rejected by finance'   => 'orange',
    ];

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request) {
        $date = trim($request->get('date'));
        $user = trim($request->get('user'));
        $search = trim($request->get('search'));

        if(auth()->user()->can('trip finance approver') || auth()->user()->hasRole('superadmin')) { // for finance view or administrators
            $trips = ActivityPlanDetailTrip::orderBy('id', 'DESC')
                ->when(!empty($date), function($query) use($date) {
                    $query->where(function($qry) use($date) {
                        $qry->where('departure', $date)
                            ->orWhere('return', $date);
                    });
                })
                ->when(!empty($user), function($query) use($user) {
                    $query->where('user_id', $user);
                })
                ->when(!empty($search), function($query) use($search) {
                    $query->where(function($qry) use($search) {
                        $qry->where('from', 'like', '%'.$search.'%')
                            ->orWhere('to', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%');
                    });
                })
                ->paginate(10)->onEachSide(1)
                ->appends(request()->query());
        } else { // users entry and user subordinate entries
            // get subordinates of the user.

            // check if user is admin of a department
            $departments = Department::where('department_admin_id', auth()->user()->id)->get();
            // get all users under departments
            $users_ids = array();
            foreach($departments as $department) {
                $users = $department->users;
                foreach($users as $user) {
                    $users_ids[] = $user->id;
                }
            }
            $users_ids = array_unique($user_ids);

            $trips = ActivityPlanDetailTrip::orderBy('id', 'DESC')
                ->where(function($query) use($users_ids) {
                    $query->where('user_id', auth()->user()->id)
                        ->orWhereIn('user_id', $users_ids);
                })
                ->when(!empty($search), function($query) use($search) {
                    $query->where(function($qry) use($search) {
                        $qry->where('from', 'like', '%'.$search.'%')
                            ->orWhere('to', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%');
                    });
                })
                ->paginate(10)->onEachSide(1)
                ->appends(request()->query());
        }

        return view('trips.index')->with([
            'user' => $user,
            'search' => $search,
            'date' => $date,
            'trips' => $trips,
            'status_arr' => $this->status_arr,
        ]);
    }

    public function list(Request $request) {
        $date = trim($request->get('date'));
        $user = trim($request->get('user'));
        $search = trim($request->get('search'));

        $subordinates = auth()->user()->getSubordinateIds();
        $subordinate_ids = [];
        foreach($subordinates as $level => $ids) {
            foreach($ids as $id) {
                $subordinate_ids[] = $id;
            }
        }

        $trips = ActivityPlanDetailTrip::with('schedule', 'schedule.user')
            ->whereHas('schedule', function ($query) use ($user, $date) {
                $query->when($user, function ($qry) use ($user) {
                        $qry->where('user_id', $user);
                    })
                    ->when($date, function ($qry) use ($date) {
                        $qry->where('date', $date);
                    });
            })
            ->where('transportation_type', 'AIR')
            ->where('source', 'schedule')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($qry) use ($search) {
                    $qry->where('departure', 'like', '%' . $search . '%')
                        ->orWhere('arrival', 'like', '%' . $search . '%')
                        ->orWhere('trip_number', 'like', '%'.$search.'%')
                        ->when($search == 'for approval', function ($qry) {
                            $qry->orWhereNull('status');
                        }, function ($qry) use ($search) {
                            $qry->orWhere('status', 'like', '%' . $search . '%');
                        });
                });
            })
            ->paginate($this->setting->data_per_page)->onEachSide(1)
            ->appends(request()->query());

        // user filters
        $users = User::whereHas('activity_plans', function($query) {
            $query->where('status', 'approved')
                ->whereHas('details', function($qry) {
                    $qry->whereHas('trip');
                });
        })
        ->when(!empty($subordinate_ids), function($query) {
            $query->whereIn('id', $subordinate_ids);
        })
        ->get();

        $user_arr = [
            '' => 'ALL USER'
        ];
        foreach($users as $user) {
            $user_arr[$user->id] = $user->fullName();
        }
        
        return view('trips.list')->with([
            'trips' => $trips,
            'users' => $user_arr,
            'user' => $user,
            'search' => $search,
            'date' => $date,
        ]);
    }

    public function show($id) {
        $status_arr = [
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        $trip = ActivityPlanDetailTrip::with('activity_plan_detail', 'approvals')->findOrFail($id);

        $approval_dates = ActivityPlanDetailTripApproval::select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->where('activity_plan_detail_trip_id', $trip->id)
            ->paginate(5, ['*'], 'approval-dates-page');

        $approval_data = [];
        foreach($approval_dates as $data) {
            $approvals = ActivityPlanDetailTripApproval::with('user')
                ->orderBy('created_at', 'DESC')
                ->where('activity_plan_detail_trip_id', $trip->id)
                ->where(DB::raw('DATE(created_at)'), $data->date)
                ->get();
            
            $approval_data[$data->date] = $approvals;
        }

        return view('trips.show')->with([
            'trip' => $trip,
            'approval_dates' => $approval_dates,
            'approvals' => $approval_data,
            'status_arr' => $status_arr,
        ]);
    }

    public function submitApprove(Request $request, $id) {
        $request->validate([
            'status' => 'required',
        ]);

        $trip = ActivityPlanDetailTrip::findOrFail($id);

        $changes_arr['old'] = $trip->getOriginal();
        
        // update status
        $trip->update([
            'status' => $request->status
        ]);

        $changes_arr['changes'] = $trip->getChanges();

        // record approvals history
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $trip->id,
            'status' => $request->status,
            'remarks' => $request->remarks
        ]);
        $approval->save();

        if($trip->source == 'activity-plan') {
            if($request->status == 'approved') {
                $detail = $trip->activity_plan_detail;
                $activity_plan = $detail->activity_plan;
                
                // convert to schedules
                $schedule = UserBranchSchedule::updateOrInsert([
                    'user_id' => $activity_plan->user_id,
                    'branch_id' => $detail->branch_id,
                    'date' => $detail->date,
                    'activity_plan_detail_trip_id' => $trip->id,
                ], [
                    'status' => NULL,
                    'objective' => $detail->activity,
                    'source' => 'activity-plan',
                ]);
            }

            $user = $activity_plan->user;
        } else {
            $user = $trip->schedule->user;
        }

        if($request->status == 'approved') {
            // logs
            activity('update')
                ->performedOn($trip)
                ->withProperties($changes_arr)
                ->log(':causer.firstname :causer.lastname has approved trip [ :subject.trip_number ].');

            // notifications
            Notification::send($user, new TripApproved($trip));
        } else {
            // logs
            activity('update')
                ->performedOn($trip)
                ->withProperties($changes_arr)
                ->log(':causer.firstname :causer.lastname has rejected trip [ :subject.trip_number ].');

            // notifications
            Notification::send($user, new TripRejected($trip));
        }

        return back()->with([
            'message_success' => 'Trip '.$trip->trip_number.' has been '.$request->status.'.'
        ]);
    }

    public function approve($id) {
        $trip = ActivityPlanDetailTrip::findOrFail($id);

        $changes_arr['old'] = $trip->getOriginal();
        
        // update status
        $trip->update([
            'status' => 'approved'
        ]);

        $changes_arr['changes'] = $trip->getChanges();

        // record approvals history
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $trip->id,
            'status' => 'approved',
        ]);
        $approval->save();

        if($trip->source == 'activity-plan') {
            $detail = $trip->activity_plan_detail;
            $activity_plan = $detail->activity_plan;
            $user = $activity_plan->user;
    
            // convert to schedules
            $schedule = UserBranchSchedule::updateOrInsert([
                'user_id' => $activity_plan->user_id,
                'branch_id' => $detail->branch_id,
                'date' => $detail->date,
                'activity_plan_detail_trip_id' => $trip->id,
            ], [
                'status' => NULL,
                'objective' => $detail->activity,
                'source' => 'activity-plan',
            ]);
        } else {
            $user = $trip->schedule->user;
        }

        // logs
        activity('update')
            ->performedOn($trip)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has approved trip [ :subject.trip_number ].');

        // notifications
        Notification::send($user, new TripApproved($trip));

        return back()->with([
            'message_success' => 'Trip '.$trip->trip_number.' has been approved.'
        ]);
    }

    public function reject($id) {
        $trip = ActivityPlanDetailTrip::findOrFail($id);

        $changes_arr['old'] = $trip->getOriginal();

        // update status
        $trip->update([
            'status' => 'rejected'
        ]);

        $changes_arr['changes'] = $trip->getChanges();

        // record approvals history
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $trip->id,
            'status' => 'rejected',
        ]);
        $approval->save();

        if($trip->source == 'activity-plan') {
            $user = $trip->activity_plan_detail->activity_plan->user;
        } else {
            $user = $trip->schedule->user;
        }

        // logs
        activity('update')
            ->performedOn($trip)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has rejected trip [ :subject.trip_number ].');

        // notifications
        Notification::send($user, new TripRejected($trip));
        
        return back()->with([
            'message_success' => 'Trip '.$trip->trip_number.' has been rejected.'
        ]);
    }

    public function create() {
        return view('trips.create');
    }

    public function edit($id) {
        $trip = ActivityPlanDetailTrip::findOrFail($id);

        return view('trips.edit')->with([
            'trip' => $trip
        ]);
    }
}
