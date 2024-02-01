<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;

use App\Models\ActivityPlanDetailTrip;

class TripCreate extends Component
{
    public $type;
    public $trip_number;
    public $from, $to, $departure, $return, $passenger;

    public function submitTrip() {
        $this->validate([
            'from' => [
                'required'
            ],
            'to' => [
                'required'
            ],
            'departure' => [
                'required'
            ],
            'return' => [
                function ($attribute, $value, $fail) {
                    if ($this->type == 'round_trip' && empty($value)) {
                        $fail('The return field is required for round trips.');
                    }
                }
            ]
        ]);
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

    public function mount() {
        $this->type = 'one_way';
        $this->generateTripNumber();
        $this->passenger = 1;
    }

    public function render()
    {
        return view('livewire.trip.trip-create');
    }
}
