<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;

use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripSubmitted;

use Carbon\Carbon;

class TripEdit extends Component
{
    public $type;
    public $trip;
    public $from, $to, $departure, $return, $passenger;
    public $form_errors;
    public $status_arr = [
        'submitted'             => 'secondary',
        'for revision'          => 'warning',
        'approved'              => 'primary',
        'returned'              => 'danger',
        'for approval'          => 'info',
        'approved by finance'   => 'success',
        'rejected by finance'   => 'orange',
    ];

    public function submitTrip() {
        $this->validate([
            'from' => [
                'required'
            ],
            'to' => [
                'required'
            ],
            'departure' => [
                'required',
                function ($attribute, $value, $fail) {
                    $currentDate = Carbon::today();
                    $departureDate = Carbon::parse($value);

                    if($currentDate > $departureDate) {
                        $fail('Ensure that the departure date is set after the current date.');
                    }
                }
            ],
            'return' => [
                function ($attribute, $value, $fail) {
                    if ($this->type == 'round_trip' && empty($value)) {
                        $fail('The return field is required for round trips.');
                    }

                    $currentDate = Carbon::today();
                    $departureDate = Carbon::parse($this->departure);
                    $returnDate = Carbon::parse($value);
                    if($currentDate > $departureDate) {
                        $fail('Ensure that the departure date is set after the current date.');
                    }

                    if(!empty($this->departure) && !empty($value) && $departureDate > $returnDate) {
                        $fail('The return date must be on or before the departure date.');
                    }
                }
            ]
        ]);

        $this->trip->update([
            'from' => $this->from,
            'to' => $this->to,
            'departure' => $this->departure,
            'return' => $this->type == 'round_trip' ? $this->return : NULL,
            'trip_type' => $this->type,
            'transportation_type' => 'AIR',
            'passenger' => $this->passenger,
            'status' => 'submitted',
        ]);

        // approval history
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $this->trip->id,
            'status' => 'submitted',
            'remarks' => NULL,
        ]);
        $approval->save();

        // notfications
        // get user department admin for notfication
        $department = auth()->user()->department;
        if(!empty($department)) {
            $admin = $department->department_head;
            if(!empty($admin) && $admin->id != auth()->user()->id) {
                Notification::send($admin, new TripSubmitted($this->trip));
            }
        }

        // systemlog
        activity('created')
            ->performedOn($this->trip)
            ->log(':causer.firstname :causer.lastname submitted a trip :subject.trip_number');

        return redirect()->route('trip.index')->with([
            'message_success' => 'Trip has been updated with ticket number: '.$this->trip->ticket_number
        ]);
    }

    public function updatedDeparture() {
        $this->validateDate();
    }

    private function validateDate() {
        if (!empty($this->departure)) {
            $currentDate = Carbon::today();
            $departureDate = Carbon::parse($this->departure);
            if($departureDate->diffInDays($currentDate) < 14) {
                // Show a note for the user
                $this->form_errors = 'The date provided should be at least two weeks in advance to facilitate ticket processing.';
            } else {
                $this->reset('form_errors');
            }
        }
    }

    public function switch() {
        $from = $this->from;
        $to = $this->to;

        $this->from = $to;
        $this->to = $from;
    }

    public function selectType($type) {
        $this->type = $type;
    }

    public function mount($trip) {
        $this->trip = $trip;
        $this->from = $trip->from;
        $this->to = $trip->to;
        $this->departure = $trip->departure;
        $this->return = $trip->return;
        $this->passenger = $trip->passenger;
        $this->type = $trip->trip_type;
    }

    public function render()
    {
        return view('livewire.trip.trip-edit');
    }
}
