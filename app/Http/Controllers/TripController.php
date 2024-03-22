<?php

namespace App\Http\Controllers;

use App\Http\Traits\GlobalTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\ActivityPlanDetailTripAttachment;
use App\Models\UserBranchSchedule;
use App\Models\Department;
use App\Models\DepartmentStructure;

use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripForRevision;
use App\Notifications\TripApprovedSuperior;
use App\Notifications\TripReturned;
use App\Notifications\TripForApproval;
use App\Notifications\TripApproved;
use App\Notifications\TripRejected;
use App\Notifications\TripCancelled;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TripExport;

class TripController extends Controller
{
    use GlobalTrait;

    public $setting;
    public $status_arr = [
        'draft'                     => 'secondary',
        'submitted'                 => 'indigo',
        'for revision'              => 'warning',
        'approved by imm. superior' => 'primary',
        'returned'                  => 'orange',
        'for approval'              => 'info',
        'approved by finance'       => 'success',
        'rejected by finance'       => 'danger',
        'cancelled'                 => 'maroon',
    ];

    public $status_responsible_arr = [
        'draft'                     => 'Filer',
        'submitted'                 => 'Immediate Superior',
        'for revision'              => 'Filer',
        'approved by imm. superior' => 'Admin',
        'returned'                  => 'Filer',
        'for approval'              => 'Finance',
        'approved by finance'       => '-',
        'rejected by finance'       => '-',
        'cancelled'                 => '-',
    ];

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request) {
        $date = trim($request->get('date'));
        $user = trim($request->get('user'));
        $search = trim($request->get('search'));

        $query_arr = array();
        if(!empty($date)) {
            $query_arr[] = 'date='.$date;
        }
        if(!empty($user)) {
            $query_arr[] = 'user='.$user;
        }
        if(!empty($search)) {
            $query_arr[] = 'search='.$search;
        }
        $query_string = implode('&', $query_arr);

        $users_arr = array();
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
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhere('trip_number', 'like', '%'.$search.'%');
                    });
                })
                ->paginate(10)->onEachSide(1)
                ->appends(request()->query());

            $users = User::whereHas('trips', function($query) use($date, $search) {
                $query->when(!empty($date), function($qry) use($date) {
                        $qry->where('departure', $date)
                            ->orWhere('return', $date);
                    })
                    ->when(!empty($search), function($qry) use($search) {
                        $qry->where('status', 'like', '%'.$search.'%')
                            ->orWhere('trip_number', 'like', '%'.$search.'%')
                            ->orWhere('from', 'like', '%'.$search.'%')
                            ->orWhere('to', 'like', '%'.$search.'%');
                    });
            })
            ->get();

            foreach($users as $user) {
                $users_arr[$user->id] = $user->fullName();
            }

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
            // get user subordiates
            $subordinate_ids = auth()->user()->getSubordinateIds();
            if(!empty($subordinate_ids)) {
                foreach($subordinate_ids as $level => $ids) {
                    foreach($ids as $id) {
                        $users_ids[] = $id;
                    }
                }
            }

            // get subordinates
            $structures = DepartmentStructure::where('user_id', auth()->user()->id)
                ->get();
            if(!empty($structures)) {
                foreach($structures as $structure) {
                    $structure_sub = DepartmentStructure::whereRaw('FIND_IN_SET('.$structure->id.', reports_to_ids) > 0')
                        ->get();
                    if(!empty($structure_sub)) {
                        foreach($structure_sub as $sub) {
                            $users_ids[] = $sub->user_id;
                        }
                    }
                }
            }

            $users_ids = array_unique($users_ids);

            $trips = ActivityPlanDetailTrip::orderBy('id', 'DESC')
                ->where(function($query) use($users_ids) {
                    $query->where('user_id', auth()->user()->id)
                        ->orWhereIn('user_id', $users_ids);
                })
                ->when(!empty($search), function($query) use($search) {
                    $query->where(function($qry) use($search) {
                        $qry->where('from', 'like', '%'.$search.'%')
                            ->orWhere('to', 'like', '%'.$search.'%')
                            ->orWhere('status', 'like', '%'.$search.'%')
                            ->orWhere('trip_number', 'like', '%'.$search.'%');
                    });
                })
                ->paginate(10)->onEachSide(1)
                ->appends(request()->query());

            $users = User::whereHas('trips', function($query) use($date, $search) {
                $query->when(!empty($date), function($qry) use($date) {
                        $qry->where('departure', $date)
                            ->orWhere('return', $date);
                    })
                    ->when(!empty($search), function($qry) use($search) {
                        $qry->where('status', 'like', '%'.$search.'%')
                            ->orWhere('trip_number', 'like', '%'.$search.'%')
                            ->orWhere('from', 'like', '%'.$search.'%')
                            ->orWhere('to', 'like', '%'.$search.'%');
                    });
            })
            ->whereIn('id', $users_ids)
            ->get();

            foreach($users as $user) {
                $users_arr[$user->id] = $user->fullName();
            }
        }

        return view('trips.index')->with([
            'user' => $user,
            'search' => $search,
            'date' => $date,
            'trips' => $trips,
            'status_arr' => $this->status_arr,
            'users' => $users_arr,
            'status_responsible_arr' => $this->status_responsible_arr,
            'query_string' => $query_string
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
            'status_responsible_arr' => $this->status_responsible_arr
        ]);
    }

    public function show($id) {
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

        $user = $trip->user;
        $department = $user->department;
        $supervisor_ids = array();
        $admin = NULL;
        if(!empty($department)) {
            // get admin
            $admin = $department->department_admin;
            // get supervisor
            $structures = DepartmentStructure::where('department_id', $department->id)
                ->where('user_id', $user->id)
                ->get();
                
            foreach($structures as $structure) {
                $reports_to_ids = explode(',', $structure->reports_to_ids);
                $supervisors = DepartmentStructure::whereIn('id', $reports_to_ids)
                    ->get();

                foreach($supervisors as $visor) {
                    if(!in_array($visor->user_id, $supervisor_ids)) {
                        $supervisor_ids[] = $visor->user_id;
                    }
                }
            }
        }

        // get user supervisors
        $supervisors_arr = $user->getSupervisorIds();
        if(!empty($supervisors_arr['first'])) {
            foreach($supervisors_arr as $level => $id) {
                if(!empty($id)) {
                    $supervisor_ids[] = $id;
                    break;
                }
            }
        }
        $supervisor_ids = array_unique($supervisor_ids);

        return view('trips.show')->with([
            'trip' => $trip,
            'approval_dates' => $approval_dates,
            'approvals' => $approval_data,
            'status_arr' => $this->status_arr,
            'supervisor_ids' => $supervisor_ids,
            'admin' => $admin,
            'department' => $department,
        ]);
    }

    public function submitApprove(Request $request, $id) {
        $request->validate([
            'status' => 'required',
            'amount' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->status == 'for approval' && empty($value)) {
                        $fail('Amount is required when status is "for approval".');
                    }
                }
            ],
            'remarks' => [
                function($attribute, $value, $fail) use($request) {
                    if(($request->status == 'for approval' || $request->status == 'returned' || $request->status == 'for revision' || $request->status == 'rejected by finance' || $request->status == 'cancelled') && empty($value)) {
                        $fail('Remarks is required.');
                    }
                }
            ]
        ]);

        $trip = ActivityPlanDetailTrip::findOrFail($id);

        // check if there's attachment
        if($request->status == 'for approval') {
            $attachment_count = $trip->attachments()->where('title', '<>', 'TRIP ATTACHMENT')->count();
            if(empty($attachment_count)) {
                return back()->with([
                    'message_error' => 'Before proceeding, kindly include the necessary attachment.'
                ]);

            }
        }

        $changes_arr['old'] = $trip->getOriginal();

        // update status
        $trip->update([
            'status' => $request->status,
            'amount' => $request->amount ?? $trip->amount,
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

        $user = $trip->user;
        $department = $user->department;
        $supervisor_ids = array();
        $admin = NULL;
        if(!empty($department)) {
            // get admin
            $admin = $department->department_admin;
            // get supervisor
            $structures = DepartmentStructure::where('department_id', $department->id)
                ->where('user_id', $user->id)
                ->get();
                
            foreach($structures as $structure) {
                $reports_to_ids = explode(',', $structure);
                $supervisors = DepartmentStructure::whereIn('id', $reports_to_ids)
                    ->get();

                foreach($supervisors as $visor) {
                    if(!in_array($visor->user_id, $supervisor_ids)) {
                        $supervisor_ids[] = $visor->user_id;
                    }
                }
            }
        }
        
        // for revision
        if($trip->status == 'for revision') {
            Notification::send($user, new TripForRevision($trip));
        }
        // approved by imm. superior
        if($trip->status == 'approved by imm. superior') {
            Notification::send($user, new TripApprovedSuperior($trip));
            if(!empty($admin)) {
                Notification::send($admin, new TripApprovedSuperior($trip));
            }
        }
        // returned
        if($trip->status == 'returned') {
            Notification::send($trip->user, new TripReturned($trip));
        }
        // for approval
        if($trip->status == 'for approval') {
            // get trip request finance approver
            $users_arr = User::whereHas('roles', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('name', 'trip finance approver');
                });
            })->get();

            $users[] = $trip->user;
            foreach($users_arr as $user) {
                $users[] = $user;
            }

            foreach($users as $user) {
                if($user->id != auth()->user()->id) {
                    Notification::send($user, new TripForApproval($trip));
                }
            }
        }
        // approved by finance
        if($trip->status == 'approved by finance') {
            Notification::send($user, new TripApproved($trip));
            if(!empty($admin)) {
                Notification::send($admin, new TripApproved($trip));
            }
            if(!empty($supervisor_ids)) {
                foreach($supervisor_ids as $user_id) {
                    $superior = User::findOrFail($user_id);
                    if(!empty($superior)) {
                        Notification::send($superior, new TripApproved($trip));
                    }
                }
            }
        }
        // rejected by finance
        if($trip->status == 'rejected by finance') {
            Notification::send($user, new TripRejected($trip));
            if(!empty($admin)) {
                Notification::send($admin, new TripRejected($trip));
            }
            if(!empty($supervisor_ids)) {
                foreach($supervisor_ids as $user_id) {
                    $superior = User::findOrFail($user_id);
                    Notification::send($superior, new TripRejected($trip));
                }
            }
        }
        // cancelled
        if($trip->status == 'cancelled') {
            // finance approvers
            $users_arr = User::whereHas('roles', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('name', 'trip finance approver');
                });
            })->get();

            $users[] = $trip->user;
            $users[] = $admin;
            foreach($users_arr as $user) {
                $users[] = $user;
            }

            foreach($users as $user) {
                if($user->id != auth()->user()->id) {
                    Notification::send($user, new TripCancelled($trip));
                }
            }
        }

        // logs
        activity('update')
            ->performedOn($trip)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated trip [ :subject.trip_number ].');

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

        if($trip->status == 'for revision' || $trip->status == 'returned' || $trip->status == 'draft') {
            return view('trips.edit')->with([
                'trip' => $trip
            ]);
        } else {
            return redirect()->route('trip.index')->with([
                'message_error' => 'This trip with status '.$trip->status.' cannot be edited.'
            ]);
        }
    }

    public function attach(Request $request, $id) {
        // validate form data
        $request->validate([
            'attachment_file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            ],
            'title' => [
                'required',
            ],
            'description' => [
                'max:1500'
            ]
        ]);
        
        $trip = ActivityPlanDetailTrip::findOrFail($id);

        // make directory if do not exist
        $file = $request->file('attachment_file');
        $filename = time().'-'.$file->getClientOriginalName();
        $file->storeAs('uploads/trip-attachments/'.$trip->id, $filename, 'public');

        $trip_attachment = new ActivityPlanDetailTripAttachment([
            'activity_plan_detail_trip_id' => $trip->id,
            'title' => $request->title,
            'description' => $request->description,
            'url' => $filename
        ]);
        $trip_attachment->save();

        // logs
        activity('create')
            ->performedOn($trip)
            ->log(':causer.firstname :causer.lastname has added an attachment to trip :subject.trip_number');

        return back()->with([
            'message_success' => 'Attachment has been added.'
        ]);
    }

    public function trip_user($id) {
        $id = decrypt($id, 'user-id');

        $user = User::findOrFail($id);

        $trips = ActivityPlanDetailTrip::where('user_id', $id)
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());
        
        return view('trips.user-page')->with([
            'trips' => $trips,
            'user' => $user,
            'status_arr' => $this->status_arr,
        ]);
        
    }

    public function trip_user_detail($id) {
        $id = decrypt($id, 'trip-id');

        $trip = ActivityPlanDetailTrip::findOrFail($id);

        return view('trips.user-trip-detail')->with([
            'trip' => $trip,
            'status_arr' => $this->status_arr,
        ]);
    }

    public function export(Request $request) {
        $search = trim($request->get('search'));
        $date = $request->get('date');
        $user_id = $request->get('user');

        return Excel::download(new TripExport($search, $date, $user_id), 'SMS Trip Request List'.time().'.xlsx');
    }
}
