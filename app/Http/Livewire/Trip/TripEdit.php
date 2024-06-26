<?php

namespace App\Http\Livewire\Trip;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\User;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripApproval;
use App\Models\ActivityPlanDetailTripDestination;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TripSubmitted;

use Carbon\Carbon;

class TripEdit extends Component
{
    use WithFileUploads;

    public $type;
    public $trip;
    public $from, $to, $departure, $return, $passenger, $purpose, $attachment, $status;
    public $trip_attachment;
    public $form_errors;
    public $passenger_other, $from_other, $to_other, $departure_other, $return_other;
    public $users;

    public $status_arr = [
        'draft'                     => 'secondary',
        'submitted'                 => 'indigo',
        'for revision'              => 'warning',
        'approved by imm. superior' => 'primary',
        'returned'                  => 'orange',
        'for approval'              => 'info',
        'approved by finance'       => 'success',
        'rejected by finance'       => 'danger',
    ];

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
            ],
            'purpose' => [
                'required'
            ],
            'attachment' => [
                function($attribute, $value, $fail) {
                    if(!empty($this->form_errors) && empty($value) && empty($this->trip_attachment)) {
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
                function ($attribute, $values, $fail) {
                    if($this->type == 'round_trip') {
                        foreach($values as $key => $value) {
                            try {
                                if (empty($value)) {
                                    $fail('The return field is required for round trips.');
                                }
                
                                $currentDate = Carbon::today();
                                $departureDate = Carbon::parse($this->departure_other[$key]);
                                $returnDate = Carbon::parse($value);
                
                                if ($this->type == 'round_trip') {
                                    if ($currentDate > $departureDate) {
                                        $fail('The departure date must be after today.');
                                    }
                
                                    if (!empty($this->departure_other[$key]) && !empty($value) && $departureDate > $returnDate) {
                                        $fail('The return date must be on or after the departure date.');
                                    }
                                }
                            } catch (\Exception $e) {
                                $fail('The return date is not a valid date.');
                            }
                        }
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
            'purpose' => $this->purpose,
            'status' => $this->status,
        ]);

        if($this->passenger >= 2) {
            $this->trip->destinations()->forceDelete();
            foreach($this->from_other as $key => $from) {
                if(!empty($from) && !empty($this->to_other[$key]) && !empty($this->departure_other[$key]) && !empty($this->passenger_other[$key])) {
                    if($this->type == 'round_trip' && !empty($this->return_other[$key])) {
                        $destination = new ActivityPlanDetailTripDestination([
                            'activity_plan_detail_trip_id' => $this->trip->id,
                            'user_id' => $this->passenger_other[$key],
                            'from' => $this->from_other[$key],
                            'to' => $this->to_other[$key],
                            'departure' => $this->departure_other[$key],
                            'return' => $this->return_other[$key],
                        ]);
                        $destination->save();
                    } else {
                        $destination = new ActivityPlanDetailTripDestination([
                            'activity_plan_detail_trip_id' => $this->trip->id,
                            'user_id' => $this->passenger_other[$key],
                            'from' => $this->from_other[$key],
                            'to' => $this->to_other[$key],
                            'departure' => $this->departure_other[$key],
                            'return' => NULL,
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
                'activity_plan_detail_trip_id' => $this->trip->id,
                'status' => $this->status,
                'remarks' => NULL,
            ]);
            $approval->save();
        }

        // check if below 2 weeks
        if(!empty($this->form_errors) && !empty($this->attachment)) {
            if(empty($this->trip_attachment)) {
                // save attachment
                $file = $this->attachment;
                $filename = time().'-'.$file->getClientOriginalName();
                $file->storeAs('uploads/trip-attachments/'.$this->trip->id, $filename, 'public');
        
                $trip_attachment = new ActivityPlanDetailTripAttachment([
                    'activity_plan_detail_trip_id' => $this->trip->id,
                    'title' => 'TRIP ATTACHMENT',
                    'description' => '',
                    'url' => $filename
                ]);
                $trip_attachment->save();
            } else {
                // update attachment
                // remove previous attachment
                $old_filename = $this->trip_attachment->url;
                $this->deleteAttachment($this->trip->id, $old_filename);

                $file = $this->attachment;
                $filename = time().'-'.$file->getClientOriginalName();
                $file->storeAs('uploads/trip-attachments/'.$this->trip->id, $filename, 'public');

                $this->trip_attachment->update([
                    'url' => $filename
                ]);
            }
            
        }

        if($this->status == 'submitted') {
            // notfications
            // get user department admin or superior for notfication
            $department = auth()->user()->department;
            if(!empty($department)) {
                // notify superior if in sales department
                if(strtolower($department->department_name) == 'sales department') {
                    $superior_ids = $this->trip->user->getDepartmentSupervisorIds();
                    if(!empty($superior_ids)) {
                        foreach($superior_ids as $user_id) {
                            $superior = User::find($user_id);
                            if(!empty($superior)) {
                                Notification::send($superior, new TripSubmitted($this->trip));
                            }
                        }
                    }
                } else { // if not in sales department notify admin
                    $admin = $department->department_admin;
                    if(!empty($admin) && $admin->id != auth()->user()->id) {
                        Notification::send($admin, new TripSubmitted($this->trip));
                    }
                }
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

    private function deleteAttachment($trip_id, $filename) {
        $path = 'uploads/trip-attachments/'.$trip_id.'/'.$filename;
        if(Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
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
        $this->purpose = $trip->purpose;

        $this->trip_attachment = $this->trip->attachments()->where('title', 'TRIP ATTACHMENT')->first();

        if(!empty($this->trip->destinations()->count())) {
            foreach($this->trip->destinations as $key => $destination) {
                $key += 2;
                $this->passenger_other[$key] = $destination->user_id;
                $this->from_other[$key] = $destination->from;
                $this->to_other[$key] = $destination->to;
                $this->departure_other[$key] = $destination->departure;
                $this->return_other[$key] = $destination->return;
            }
        }

        $this->validateDate();

        $this->users = User::orderBy('firstname', 'ASC')
            ->get();
    }

    public function render()
    {
        return view('livewire.trip.trip-edit');
    }
}
