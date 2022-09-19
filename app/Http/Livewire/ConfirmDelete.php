<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

use App\Models\Company;

class ConfirmDelete extends Component
{

    public $password;
    public $error_message;
    public $model;
    public $name;
    public $route;

    protected $listeners = [
        'setDeleteModel' => 'setModel'
    ];

    public function submitForm() {
        $this->error_message = '';

        $this->validate([
            'password' => 'required'
        ]);

        // check password
        if(!Hash::check($this->password, auth()->user()->password)) { // invalid
            $this->error_message = 'incorrect password.';
        } else { // delete function
            $this->model->forceDelete();

            return redirect()->to($this->route)->with([
                'Message Success '.$this->name.' was deleted.'
            ]);
        }

    }

    public function setModel($type, $model_id) {
        switch($type) {
            case 'Company':
                $this->model = Company::findOrFail($model_id);
                $this->name = $this->model->name;
                $this->route = '/company';
                break;
        }
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
