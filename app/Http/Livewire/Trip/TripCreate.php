<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\User;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\ActivityPlanDetailTripAttachment;
use App\Models\ActivityPlanDetailTripDestination;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TripSubmitted;

use Carbon\Carbon;

class TripCreate extends Component
{
    use WithFileUploads;

    public $type;
    public $trip_number;
    public $from, $to, $departure, $return, $passenger, $purpose, $attachment, $status;
    public $form_errors;
    public $passenger_other, $from_other, $to_other, $departure_other, $return_other;
    public $users;

    public function submitForm($status) {
        $this->status = $status;
        $this->submitTrip();
    }

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
                    try {
                        $currentDate = Carbon::today();
                        $departureDate = Carbon::parse($value);
        
                        if ($currentDate >= $departureDate) {
                            $fail('The departure date must be after today.');
                        }
                    } catch (\Exception $e) {
                        $fail('The departure date is not a valid date.');
                    }
                },
            ],
            'return' => [
                function ($attribute, $value, $fail) {
                    try {
                        if ($this->type == 'round_trip' && empty($value)) {
                            $fail('The return field is required for round trips.');
                        }
        
                        $currentDate = Carbon::today();
                        $departureDate = Carbon::parse($this->departure);
                        $returnDate = Carbon::parse($value);
        
                        if ($this->type == 'round_trip') {
                            if ($currentDate > $departureDate) {
                                $fail('The departure date must be after today.');
                            }
        
                            if (!empty($this->departure) && !empty($value) && $departureDate > $returnDate) {
                                $fail('The return date must be on or after the departure date.');
                            }
                        }
                    } catch (\Exception $e) {
                        $fail('The return date is not a valid date.');
                    }
                }
            ],
            'passenger' => [
                'required'
            ],
            'purpose' => [
                'required'
            ],
            'attachment' => [
                function($attribute, $value, $fail) {
                    if(!empty($this->form_errors) && empty($value)) {
                        $fail('Attachment is required when the departure date is less than two weeks away from the current date.');
                    }
                },
            ],
            'passenger_other' => [
                function($attribute, $value, $fail) {
                    if ($this->passenger >= 2 && empty($value)) {
                        $fail('The Passenger Name field is required for additional passengers.');
                    }
                }
            ],
            'from_other' => [
                function($attribute, $value, $fail) {
                    if ($this->passenger >= 2 && empty($value)) {
                        $fail('The From field is required for additional passengers.');
                    }
                }
            ],
            'to_other' => [
                function($attribute, $value, $fail) {
                    if ($this->passenger >= 2 && empty($value)) {
                        $fail('The To field is required for additional passengers.');
                    }
                }
            ],
            'departure_other' => [
                'required',
                function ($attribute, $values, $fail) {
                    foreach($values as $value) {
                        try {
                            $currentDate = Carbon::today();
                            $departureDate = Carbon::parse($value);
            
                            if ($currentDate >= $departureDate) {
                                $fail('The departure date must be after today.');
                            }
                        } catch (\Exception $e) {
                            $fail('The departure date is not a valid date.');
                        }
                    }
                },
            ],
            'return_other' => [
                function ($attribute, $value, $fail) {
                    try {
                        if ($this->type == 'round_trip' && empty($value)) {
                            $fail('The return field is required for round trips.');
                        }
        
                        $currentDate = Carbon::today();
                        $departureDate = Carbon::parse($this->departure);
                        $returnDate = Carbon::parse($value);
        
                        if ($this->type == 'round_trip') {
                            if ($currentDate > $departureDate) {
                                $fail('The departure date must be after today.');
                            }
        
                            if (!empty($this->departure) && !empty($value) && $departureDate > $returnDate) {
                                $fail('The return date must be on or after the departure date.');
                            }
                        }
                    } catch (\Exception $e) {
                        $fail('The return date is not a valid date.');
                    }
                }
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
            'purpose' => $this->purpose,
            'status' => $this->status,
            'source' => 'trip-add',
        ]);
        $trip->save();

        // trip other destinations
        if($this->passenger >= 2) {
            foreach($this->from_other as $key => $from) {
                if(!empty($from) && !empty($this->to_other[$key]) && !empty($this->departure_other[$key]) && !empty($this->passenger_other[$key])) {
                    if($this->type == 'round_trip' && !empty($this->return_other[$key])) {
                        $destination = new ActivityPlanDetailTripDestination([
                            'activity_plan_detail_trip_id' => $trip->id,
                            'user_id' => $this->passenger_other[$key],
                            'from' => $this->from_other[$key],
                            'to' => $this->to_other[$key],
                            'departure' => $this->departure_other[$key],
                            'return' => $this->return_other[$key] ?? NULL,
                        ]);
                        $destination->save();
                    } else {
                        $destination = new ActivityPlanDetailTripDestination([
                            'activity_plan_detail_trip_id' => $trip->id,
                            'user_id' => $this->passenger_other[$key],
                            'from' => $this->from_other[$key],
                            'to' => $this->to_other[$key],
                            'departure' => $this->departure_other[$key],
                            'return' => $this->return_other[$key] ?? NULL,
                        ]);
                        $destination->save();
                    }
                }
            }
        }

        if($this->status == 'submitted') {
            // approval history
            $approval = new ActivityPlanDetailTripApproval([
                'user_id' => auth()->user()->id,
                'activity_plan_detail_trip_id' => $trip->id,
                'status' => 'submitted',
                'remarks' => NULL,
            ]);
            $approval->save();
        }

        // check if below 2 weeks
        if(!empty($this->form_errors)) {
            // save attachment
            $file = $this->attachment;
            $filename = time().'-'.$file->getClientOriginalName();
            $file->storeAs('uploads/trip-attachments/'.$trip->id, $filename, 'public');
    
            $trip_attachment = new ActivityPlanDetailTripAttachment([
                'activity_plan_detail_trip_id' => $trip->id,
                'title' => 'TRIP ATTACHMENT',
                'description' => '',
                'url' => $filename
            ]);
            $trip_attachment->save();
        }

        // notfications
        if($this->status == 'submitted') {
            // get user department admin or superior for notfication
            $department = auth()->user()->department;
            if(!empty($department)) {
                // notify superior if in sales department
                if(strtolower($department->department_name) == 'sales department') {
                    $superior_ids = $trip->user->getDepartmentSupervisorIds();
                    if(!empty($superior_ids)) {
                        foreach($superior_ids as $user_id) {
                            $superior = User::find($user_id);
                            if(!empty($superior)) {
                                Notification::send($superior, new TripSubmitted($trip));
                            }
                        }
                    }
                } else { // if not in sales department notify admin
                    $admin = $department->department_admin;
                    if(!empty($admin) && $admin->id != auth()->user()->id) {
                        Notification::send($admin, new TripSubmitted($trip));
                    }
                }
            }
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

        $this->users = User::orderBy('firstname', 'ASC')
            ->get();
    }

    public function render()
    {
        return view('livewire.trip.trip-create');
    }
}