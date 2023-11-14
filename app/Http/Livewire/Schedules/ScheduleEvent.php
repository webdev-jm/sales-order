<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;
use Livewire\WithPagination;

use AccountLoginModel;
use App\Models\User;
use App\Models\BranchLogin;
use App\Models\UserBranchSchedule;
use App\Models\UserBranchScheduleApproval;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Notification;
use App\Notifications\ScheduleDeleteRequest;
use App\Notifications\ScheduleRescheduleRequest;
use App\Notifications\TripSubmitted;

class ScheduleEvent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user_id, $account_id;
    protected $queryString = [
        'user_id',
        'account_id'
    ];
    
    public $date, $schedule_data;
    public $action;
    public $status, $reschedule_date, $remarks;

    public $accuracy, $longitude, $latitude;

    public $trip_reference_number, $reference_number_edit = 0;
    public $trip_number;
    public $departure, $arrival, $reference_number, $transportation_type;

    protected $listeners = [
        'showEvents' => 'setDate'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // update trip reference number
    public function updatedTripReferenceNumber() {
        // check if theres changes
        if($this->schedule_data->trip->reference_number != trim($this->trip_reference_number)) {
            $changes_arr = [
                'old' => [
                    'reference_number' => $this->schedule_data->trip->reference_number
                ],
                'changes' => [
                    'reference_number' => $this->trip_reference_number
                ]
            ];

            // systemlog
            activity('update')
                ->performedOn($this->schedule_data->trip)
                ->withProperties($changes_arr)
                ->log(':causer.firstname :causer.lastname has updated trip [ :subject.trip_number ]');
        }

        $this->schedule_data->trip->update([
            'reference_number' => $this->trip_reference_number
        ]);

        $this->reference_number_edit = 0;
    }

    public function editReference() {
        $this->reference_number_edit = 1;
        $this->trip_reference_number = $this->schedule_data->trip->reference_number ?? '';
    }

    public function saveEditReference() {
        // check if theres changes
        if($this->schedule_data->trip->reference_number != trim($this->trip_reference_number)) {
            $changes_arr = [
                'old' => [
                    'reference_number' => $this->schedule_data->trip->reference_number
                ],
                'changes' => [
                    'reference_number' => $this->trip_reference_number
                ]
            ];

            // systemlog
            activity('update')
                ->performedOn($this->schedule_data->trip)
                ->withProperties($changes_arr)
                ->log(':causer.firstname :causer.lastname has updated trip [ :subject.trip_number ]');
        }

        $this->schedule_data->trip->update([
            'reference_number' => $this->trip_reference_number
        ]);

        $this->reference_number_edit = 0;
    }

    // Sign In
    public function sign_in() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        // check if logged in to account or branch
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        $logged_branch = BranchLogin::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        if(empty($logged_account) && empty($logged_branch)) {
            $branch_login = new BranchLogin([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->schedule_data->branch_id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'time_in' => now(),
            ]);
            $branch_login->save();

            Session::put('logged_branch', $branch_login);

            // logs
            activity('login')
            ->performedOn($branch_login)
            ->log(':causer.firstname :causer.lastname has logged in to branch '.$this->schedule_data->branch->branch_name);
            
            return redirect()->to('/home')->with([
                'message_success' => 'You are logged in.'
            ]);
        } else {
            return redirect()->to('/home')->with([
                'message_error' => 'Your are currently logged in to a branch.'
            ]);
        }
        
    }

    // save trip
    public function submitTrip() {
        $this->validate([
            'departure' => [
                'required'
            ],
            'arrival' => [
                'required'
            ],
            'transportation_type' => [
                'required'
            ]
        ]);

        $trip = new ActivityPlanDetailTrip([
            'activity_plan_detail_id' => NULL,
            'trip_number' => $this->trip_number,
            'departure' => $this->departure,
            'arrival' => $this->arrival,
            'reference_number' => $this->reference_number ?? '',
            'transportation_type' => $this->transportation_type,
            'source' => 'schedule'
        ]);
        $trip->save();

        $this->schedule_data->update([
            'activity_plan_detail_trip_id' => $trip->id
        ]);

        // record to approvals
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $trip->id,
            'status' => 'submitted',
        ]);
        $approval->save();

        // systemlog
        activity('created')
            ->performedOn($trip)
            ->log(':causer.firstname :causer.lastname added a new trip :subject.trip_number');

        // notification
        // get all users with trip approve permission
        $permission = Permission::where('name', 'trip approve')->first();
        $users = $permission->users;
        foreach($users as $user) {
            if($user->id != auth()->user()->id) {
                Notification::send($user, new TripSubmitted($trip));
            }
        }

        return redirect(request()->header('Referer'))->with([
            'message_success' => 'Trip '.$trip->trip_number.' has been created.'
        ]);
    }

    public function loadLocation() {
        $this->dispatchBrowserEvent('reloadLocation');
    }

    // Re-schedule && Delete Request
    public function submit() {
        if($this->action == 'reschedule-request') {
            $this->validate([
                'reschedule_date' => 'required',
                'remarks' => 'required'
            ]);

            $changes_arr['old'] = $this->schedule_data->getOriginal();

            $this->schedule_data->update([
                'reschedule_date' => $this->reschedule_date,
                'status' => 'for reschedule'
            ]);

            $approval = new UserBranchScheduleApproval([
                'user_branch_schedule_id' => $this->schedule_data->id,
                'user_id' => auth()->user()->id,
                'status' => 'for reschedule',
                'remarks' => $this->remarks
            ]);
            $approval->save();

            // notifications
            // $user_ids = auth()->user()->getSupervisorIds();
            // foreach($user_ids as $user_id) {
            //     if(auth()->user()->id != $user_id) {
            //         $user = User::find($user_id);
            //         Notification::send($user, new ScheduleRescheduleRequest($this->schedule_data));
            //     }
            // }

            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if(auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new ScheduleRescheduleRequest($this->schedule_data));
                }
            }

            $changes_arr['changes'] = $this->schedule_data->getChanges();

            // logs
            activity('update')
            ->performedOn($this->schedule_data)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated schedule [ :subject.date ] .');

            return redirect(request()->header('Referer'));
        }

        if($this->action == 'delete-request') {
            $this->validate([
                'remarks' => 'required'
            ]);

            $changes_arr['old'] = $this->schedule_data->getOriginal();

            $this->schedule_data->update([
                'status' => 'for deletion'
            ]);

            $approval = new UserBranchScheduleApproval([
                'user_branch_schedule_id' => $this->schedule_data->id,
                'user_id' => auth()->user()->id,
                'status' => 'for deletion',
                'remarks' => $this->remarks
            ]);
            $approval->save();

            // notifications
            // $user_ids = auth()->user()->getSupervisorIds();
            // foreach($user_ids as $user_id) {
            //     if(auth()->user()->id != $user_id) {
            //         $user = User::find($user_id);
            //         Notification::send($user, new ScheduleDeleteRequest($this->schedule_data));
            //     }
            // }

            $supervisor_id = auth()->user()->getImmediateSuperiorId();
            if(auth()->user()->id != $supervisor_id) {
                $user = User::find($supervisor_id);
                if(!empty($user)) {
                    Notification::send($user, new ScheduleDeleteRequest($this->schedule_data));
                }
            }

            $changes_arr['changes'] = $this->schedule_data->getChanges();

            // logs
            activity('update')
            ->performedOn($this->schedule_data)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has updated schedule [ :subject.date ] .');

            return redirect(request()->header('Referer'));
        }
    }

    public function setAction($action) {
        $this->action = $action;
        $this->dispatchBrowserEvent('reloadLocation');
        if($action == 'reschedule-request') {
            $this->reschedule_date = $this->date;
        }

        // set trip number
        if($action == 'add-trip') {
            $this->reset('trip_number');
            $this->generateTripNumber();
        }
    }

    private function generateTripNumber() {
        // Check if a trip number already exists
        if (empty($this->trip_number)) {
            $new_trip_number = null;

            do {
                /// Generate a random letter
                $random_letter = chr(65 + rand(0, 25)); // A-Z

                // Generate the remaining part of the trip number (alphanumeric)
                $random_alphanumeric = strtoupper(substr(sha1(uniqid()), 0, 5));

                // Combine the letter and alphanumeric characters
                $new_trip_number = $random_letter . $random_alphanumeric;
            } while (ActivityPlanDetailTrip::where('trip_number', $new_trip_number)->exists());

            // Set the new trip number
            $this->trip_number = $new_trip_number;
        }
    }

    public function backAction() {
        $this->reset(['action', 'reschedule_date']);
    }

    public function viewSchedule($schedule_id) {
        $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);

        if(!empty($this->schedule_data->trip) && empty($this->schedule_data->trip->reference_number) && $this->schedule_data->trip->type_of_transportation == 'AIR') {
            $this->reference_number_edit = 1;
        } else {
            $this->reference_number_edit = 0;
        }

        $this->trip_reference_number = $this->schedule_data->trip->reference_number ?? '';
    }

    public function back() {
        $this->reset('schedule_data');
    }

    public function setDate($date, $schedule_id) {
        $this->date = $date;
        if(!empty($schedule_id)) {
            $this->schedule_data = UserBranchSchedule::findOrFail($schedule_id);

            if(!empty($this->schedule_data->trip) && empty($this->schedule_data->trip->reference_number) && $this->schedule_data->trip->type_of_transportation == 'AIR') {
                $this->reference_number_edit = 1;
            } else {
                $this->reference_number_edit = 0;
            }

            $this->trip_reference_number = $this->schedule_data->trip->reference_number ?? '';
        }


        // $this->reset('schedule_data');
    }

    public function render()
    {
        if(!empty($this->user_id) || !empty($this->account_id)) {
            if(auth()->user()->hasRole('superadmin')) {
                $branch_schedules = UserBranchSchedule::where('date', $this->date)
                ->whereNull('status');

                if(!empty($this->user_id)) {
                    $branch_schedules->where('user_id', $this->user_id);
                }

                if(!empty($this->account_id)) {
                    $branch_schedules->whereHas('branch', function($query) {
                        $query->where('account_id', $this->account_id);
                    });
                }

                $branch_schedules = $branch_schedules->paginate(10)->onEachSide(1);
            } else {
                $branch_schedules = UserBranchSchedule::where('date', $this->date)
                ->whereNull('status')
                ->where('user_id', auth()->user()->id);
                
                if(!empty($this->account_id)) {
                    $branch_schedules->whereHas('branch', function($query) {
                        $query->where('account_id', $this->account_id);
                    });
                }

                $branch_schedules = $branch_schedules->paginate(10)->onEachSide(1);
            }

        } else {
            if(auth()->user()->hasRole('superadmin')) {
                $branch_schedules = UserBranchSchedule::where('date', $this->date)
                ->whereNull('status')
                ->paginate(10)->onEachSide(1);
            } else {
                $branch_schedules = UserBranchSchedule::where('date', $this->date)
                ->whereNull('status')
                ->where('user_id', auth()->user()->id)
                ->paginate(10)->onEachSide(1);
            }
        }

        $transportation_types = [
            'AIR',
            'LAND'
        ];

        return view('livewire.schedules.schedule-event')->with([
            'branch_schedules' => $branch_schedules,
            'transportation_types' => $transportation_types
        ]);
    }
}
