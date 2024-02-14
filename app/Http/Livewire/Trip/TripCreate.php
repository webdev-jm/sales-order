<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;

use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripSubmitted;

use Carbon\Carbon;

class TripCreate extends Component
{
    public $type;
    public $trip_number;
    public $from, $to, $departure, $return, $passenger;
    public $form_errors;

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
            ],
            'passenger' => [
                'required'
            ]
        ]);

        $trip_number = $this->generateTripNumber();

        $trip = new ActivityPlanDetailTrip([
            'activity_plan_detail_id' => NULL,
            'user_id' => auth()->user()->id,
            'trip_number' => $trip_number,
            'from' => $this->from,
            'to' => $this->to,
            'departure' => $this->departure,
            'return' => $this->type == 'round_trip' ? $this->return : NULL,
            'amount' => NULL,
            'trip_type' => $this->type,
            'transportation_type' => 'AIR',
            'passenger' => $this->passenger,
            'status' => 'submitted',
            'source' => 'trip-add',
        ]);
        $trip->save();

        // approval history
        $approval = new ActivityPlanDetailTripApproval([
            'user_id' => auth()->user()->id,
            'activity_plan_detail_trip_id' => $trip->id,
            'status' => 'submitted',
            'remarks' => NULL,
        ]);
        $approval->save();

        // notfications
        // get user department admin for notfication
        $department = auth()->user()->department;
        if(!empty($department)) {
            $admin = $department->department_admin;
            if(!empty($admin) && $admin->id != auth()->user()->id) {
                Notification::send($admin, new TripSubmitted($trip));
            }
        }
        // notify superior if in sales department
        if(!empty($department) && strtolower($department->department_name) == 'sales department') {
            // get superiors
        }

        // systemlog
        activity('created')
            ->performedOn($trip)
            ->log(':causer.firstname :causer.lastname added a new trip :subject.trip_number');

        return redirect()->route('trip.index')->with([
            'message_success' => 'Trip has been created with ticket number: '.$trip->ticket_number
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

    private function generateTripNumber() {
        // Check if a trip number already exists
        $new_trip_number = null;

        do {
            /// Generate a random letter
            $random_letter = chr(65 + rand(0, 25)); // A-Z

            // Generate the remaining part of the trip number (alphanumeric)
            $random_alphanumeric = strtoupper(substr(sha1(uniqid()), 0, 5));

            // Combine the letter and alphanumeric characters
            $new_trip_number = $random_letter . $random_alphanumeric;
        } while (ActivityPlanDetailTrip::where('trip_number', $new_trip_number)->exists());

        return $new_trip_number;
    }

    public function mount() {
        $this->type = 'one_way';
        $this->passenger = 1;
    }

    public function render()
    {
        return view('livewire.trip.trip-create');
    }
}
