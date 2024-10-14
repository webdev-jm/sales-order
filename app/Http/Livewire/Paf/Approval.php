<?php

namespace App\Http\Livewire\Paf;

use Livewire\Component;

use App\Models\Paf;
use App\Models\Brand;
use App\Models\PafApproval;

class Approval extends Component
{
    public $action, $paf;
    public $remarks;

    protected $listeners = [
        'setPaf' => 'setPaf'
    ];

    public function setPaf($action, $paf_id) {
        $this->action = $action;
        $this->paf = Paf::findOrFail($paf_id);
    }

    public function submitApproval() {
        $this->validate([
            'remarks' => [
                'max:2000'
            ]
        ]);

        $this->paf->update([
            'status' => $this->action
        ]);

        $approval = new PafApproval([
            'paf_id' => $this->paf->id,
            'user_id' => auth()->user()->id,
            'status' => $this->action,
            'remarks' => $this->remarks
        ]);
        $approval->save();

        if($this->action == 'approved') {
            // notify brands approver
            $brands = Brand::whereHas('products', function($query) {
                $query->whereHas('paf_details', function($qry) {
                    $qry->where('paf_id', $this->paf->id);
                });
            })
            ->get();

            // get brand approvers
            $users = array();
            foreach($brands as $brand) {
                if(!empty($brand->users)) {
                    foreach($brand->users as $user) {
                        $users[$user->id] = $user;
                    }
                }
            }
            // send notification
            foreach($users as $user) {
                Notification::send($user, new PafApproved($this->paf));
            }
        }

        return redirect(request()->header('Referer'))->with([
            'message_success' => 'PAF has been updated.'
        ]);
    }

    public function render()
    {
        return view('livewire.paf.approval');
    }
}
