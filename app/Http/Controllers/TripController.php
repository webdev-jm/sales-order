<?php

namespace App\Http\Controllers;

use App\Http\Traits\GlobalTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\UserBranchSchedule;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripApproved;
use App\Notifications\TripRejected;

class TripController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request) {
        $date = trim($request->get('date'));
        $user = trim($request->get('user'));
        $search = trim($request->get('search'));

        $trips = ActivityPlanDetailTrip::with('activity_plan_detail', 'approvals')
            ->where(function($query) use($user, $date) {
                $query->whereHas('activity_plan_detail', function($query) use($user, $date) {
                    $query->whereHas('activity_plan', function($qry) use($user, $date) {
                        $qry->where('status', 'approved')
                        ->when(!empty($user), function($qry1) use($user) {
                            $qry1->where('user_id', $user);
                        });
                    })
                    ->when(!empty($date), function($qry) use($date) {
                        $qry->where('date', $date);
                    });
                })
                ->orWhereHas('schedule', function($query) use($user, $date) {
                    $query->when(!empty($user), function($qry) use($user) {
                        $qry->where('user_id', $user);
                    })
                    ->when(!empty($date), function($qry) use($date) {
                        $qry->where('date', $date);
                    });
                });
            })
            ->where('transportation_type', 'AIR')
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('departure', 'like', '%'.$search.'%')
                        ->orWhere('arrival', 'like', '%'.$search.'%')
                        ->orWhere('trip_number', 'like', '%'.$search.'%');

                    if($search == 'for approval') {
                        $qry->orWhereNull('status');
                    } else {
                        $qry->orWhere('status', 'like', '%'.$search.'%');
                    }
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
        ->get();

        $user_arr = [
            '' => 'ALL USER'
        ];
        foreach($users as $user) {
            $user_arr[$user->id] = $user->fullName();
        }

        return view('trips.index')->with([
            'trips' => $trips,
            'users' => $user_arr,
            'user' => $user,
            'search' => $search,
            'date' => $date,
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
            $approvals = ActivityPlanDetailTripApproval::orderBy('created_at', 'DESC')
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
}
