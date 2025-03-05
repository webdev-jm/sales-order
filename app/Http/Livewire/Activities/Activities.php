<?php

namespace App\Http\Livewire\Activities;

use Livewire\Component;
use App\Models\OperationProcess;
use App\Models\Activity;
use App\Models\BranchLoginActivity;

class Activities extends Component
{
    public $logged_branch, $branch, $operation_processes;
    public $operation_process_id, $activities;
    public $activity_val, $remarks, $action_points;

    public function selectOperationProcess() {
        if($this->operation_process_id != '') {
            $operation_process = OperationProcess::findOrFail($this->operation_process_id);
            $this->activities = $operation_process->activities()->orderBy('number', 'ASC')->get();
        } else {
            $this->reset('activities');
        }

        $this->reset('activity_val');

        $this->saveActivity();
    }

    public function updateData() {
        $this->saveActivity();
    }

    public function saveActivity() {
        

        $operation_process_id = $this->operation_process_id == '' ? null : $this->operation_process_id;

        $this->logged_branch->update([
            'operation_process_id' => $operation_process_id,
            'action_points' => $this->action_points ?? '',
        ]);

        if(empty($operation_process)) {
            // check
            $activity_check = BranchLoginActivity::where('branch_login_id', $this->logged_branch->id)
                ->whereNull('activity_id')
                ->first();
            if(!empty($activity_check)) {
                $activity_check->update([
                    'remarks' => $this->remarks ?? '',
                ]);
            } else {
                $branch_activity = new BranchLoginActivity([
                    'branch_login_id' => $this->logged_branch->id,
                    'activity_id' => NULL,
                    'remarks' => $this->remarks ?? '',
                ]);
                $branch_activity->save();
            }
        }

        if(!empty($this->activity_val)) {
            foreach($this->activity_val as $activity_id  => $data) {
                if(!empty($data['number'])) { // update / insert
                    // check
                    $activity_check = BranchLoginActivity::where('branch_login_id', $this->logged_branch->id)
                    ->where('activity_id', $activity_id)
                    ->first();
                    if(empty($activity_check)) { // insert
                        $branch_activity = new BranchLoginActivity([
                            'branch_login_id' => $this->logged_branch->id,
                            'activity_id' => $activity_id,
                            'remarks' => $data['remarks'] ?? ''
                        ]);
                        $branch_activity->save();
                    } else { // update
                        $activity_check->update([
                            'remarks' => $data['remarks'] ?? ''
                        ]);
                    }
                } else { // remove
                    $activity_check = BranchLoginActivity::where('branch_login_id', $this->logged_branch->id)
                    ->where('activity_id', $activity_id)
                    ->first();

                    if(!empty($activity_check)) {
                        $activity_check->delete();
                    }
                }
            }
        } else {
            // remove all
            $this->logged_branch->login_activities()->whereNotNull('activity_id')->delete();
        }

        $this->emit('setSignout');
    }

    public function mount($logged_branch) {
        $branch = $logged_branch->branch;
        $company = $branch->account->company;
        $this->operation_process_id = $logged_branch->operation_process_id;

        if(!empty($this->operation_process_id)) {
            $this->activities = Activity::orderBy('number', 'ASC')
            ->where('operation_process_id', $this->operation_process_id)
            ->get();
        }

        $branch_activity = BranchLoginActivity::where('branch_login_id', $logged_branch->id)->whereNotNull('activity_id')->get();
        $activity_val = [];
        foreach($branch_activity as $activity) {
            if(!empty($activity->activity_id)) {
                $activity_val[$activity->activity_id] = [
                    'number' => true,
                    'remarks' => $activity->remarks
                ];
            }
        }
        
        $activity = BranchLoginActivity::where('branch_login_id', $logged_branch->id)->whereNull('activity_id')->first();
        if(!empty($activity)) {
            $this->remarks = $activity->remarks;
        }

        if(!empty($activity_val)) {
            $this->activity_val = $activity_val;
        }

        $this->operation_processes = OperationProcess::where('company_id', $company->id)
            ->get();

        $this->action_points = $logged_branch->action_points;
    }

    public function render()
    {
        return view('livewire.activities.activities');
    }
}