<?php

namespace App\Http\Livewire\Brand;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\User;

class Approver extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $brand;
    public $select_users;
    public $add_form = 0;
    public $approver_id;

    public function btnAdd() {
        if($this->add_form) {
            $this->add_form = 0;
        } else {
            $this->add_form = 1;
        }
    }

    public function addApprover() {
        $this->validate([
            'approver_id' => [
                'required'
            ]
        ]);

        $this->brand->users()->attach($this->approver_id);
    }

    public function deleteApprover($approver_id) {
        $this->brand->users()->detach($approver_id);
    }

    public function mount($brand) {
        $this->brand = $brand;
        $this->select_users = User::orderBy('firstname', 'ASC')->get();
    }

    public function render()
    {
        $users = User::orderBy('firstname')
            ->whereHas('brands', function($query) {
                $query->where('id', $this->brand->id);
            })
            ->paginate(10, ['*'], 'user-page')
            ->onEachSide(1);

        return view('livewire.brand.approver')->with([
            'users' => $users
        ]);
    }
}
