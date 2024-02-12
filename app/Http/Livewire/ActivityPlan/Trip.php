<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\ActivityPlanDetailTrip;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

use Carbon\Carbon;

class Trip extends Component
{
    public $year, $month, $date, $key;
    public $trip_number;
    public $from, $to, $departure, $return, $passenger;
    public $type;
    public $form_error;

    protected $listeners = [
        'setTrip' => 'setTrip'
    ];

    public function changeType($type) {
        $this->type = $type;
    }

    public function switch() {
        $from = $this->from;
        $to = $this->to;
        $this->from = $to;
        $this->to = $from;
    }

    private function validateDate() {
        if (!empty($this->departure)) {
            $currentDate = Carbon::today();
            $departureDate = Carbon::parse($this->departure);
            if($departureDate->diffInDays($currentDate) < 14) {
                // Show a note for the user
                $this->form_error = 'The date provided should be at least two weeks in advance to facilitate ticket processing.';
            } else {
                $this->reset('form_error');
            }
        }
    }

    public function updatedDeparture() {
        $this->validateDate();
    }

    public function setTrip($year, $month, $date, $key) {
        $this->year = $year;
        $this->month = $month;
        $this->date = $date;
        $this->key = $key;

        $this->reset('type', 'from', 'to', 'departure', 'return', 'passenger', 'trip_number');

        $this->type = 'one_way';
        $this->passenger = 1;

        
        // check data from session
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data)) {
            if(isset($activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['trip'])) {
                $trip_data = $activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['trip'];
                $this->type = $trip_data['type'] ?? 'one_way';
                $this->trip_number = $trip_data['trip_number'] ?? '';
                $this->from = $trip_data['from'] ?? '';
                $this->to = $trip_data['to'] ?? '';
                $this->departure = $trip_data['departure'] ?? '';
                $this->return = $trip_data['return'] ?? '';
                $this->passenger = $trip_data['passenger'] ?? '';
            }
        }

        $this->generateTripNumber();
        
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

    public function updateSession() {
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

        $trip_arr = [
            'type' => $this->type,
            'trip_number' => $this->trip_number,
            'from' => $this->from,
            'to' => $this->to,
            'departure' => $this->departure,
            'return' => $this->return ?? '',
            'passenger' => $this->passenger,
            'transportation_type' => 'AIR',
        ];

        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data)) {
            $trip[$this->month][$this->date]['lines'][$this->key]['trip'] = $trip_arr;

            $plan_data[$this->year] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => '',
                'details' => $trip
            ];
            // initialize data
            Session::put('activity_plan_data', $plan_data);
        } else {
            $activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['trip'] = $trip_arr;
            // replace details
            Session::put('activity_plan_data', $activity_plan_data);
        }

        $this->emit('saveTrip', $this->year, $this->month, $this->date, $this->key, $trip_arr);
    }

    public function render()
    {
        $transportation_types = [
            'AIR'
        ];

        return view('livewire.activity-plan.trip')->with([
            'transportation_types' => $transportation_types
        ]);
    }
}
