<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class Trip extends Component
{
    public $year, $month, $date, $key;
    public $trip_number;
    public $departure, $arrival, $reference_number;

    protected $listeners = [
        'setTrip' => 'setTrip'
    ];

    public function setTrip($year, $month, $date, $key) {
        $this->year = $year;
        $this->month = $month;
        $this->date = $date;
        $this->key = $key;

        $this->reset('departure', 'arrival', 'reference_number', 'trip_number');
        
        // check data from session
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data)) {
            if(isset($activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['trip'])) {
                $trip_data = $activity_plan_data[$this->year]['details'][$this->month][$this->date]['lines'][$this->key]['trip'];
                $this->trip_number = $trip_data['trip_number'] ?? '';
                $this->departure = $trip_data['departure'] ?? '';
                $this->arrival = $trip_data['arrival'] ?? '';
                $this->reference_number = $trip_data['reference_number'] ?? '';
            }
        }

        // create unique 8 character trip number
        if(empty($this->trip_number)) {
            $this->trip_number = strtoupper(substr(sha1(uniqid()), 0, 8));
        }
    }

    public function updateSession() {
        $this->validate([
            'departure' => [
                'required'
            ],
            'arrival' => [
                'required'
            ],
        ]);

        $trip_arr = [
            'trip_number' => $this->trip_number,
            'departure' => $this->departure,
            'arrival' => $this->arrival,
            'reference_number' => $this->reference_number ?? ''
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
        return view('livewire.activity-plan.trip');
    }
}
