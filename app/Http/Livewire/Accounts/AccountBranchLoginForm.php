<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;

use Livewire\WithFileUploads;
use Intervention\Image\Facades\Image;

use Illuminate\Support\Facades\Session;

class AccountBranchLoginForm extends Component
{
    use WithFileUploads;

    public $logged_branch, $branch;
    public $branch_activities;
    public $activities, $picture_file;
    public $image_url;

    public $accuracy, $longitude, $latitude;

    protected $listeners = [
        'reloadActivities' => 'loadActivities'
    ];

    public function logout() {
        if($this->picture_file) {
            foreach($this->picture_file as $key => $file) {
                $this->save_image($file, $this->logged_branch->id, $key);
            }
        }

        $this->logged_branch->update([
            'time_out' => now(),
            'time_out_longitude' => $this->longitude ?? NULL,
            'time_out_latitude' => $this->latitude ?? NULL,
            'time_out_accuracy' => $this->accuracy ?? NULL
        ]);

        // logs
        activity('logout')
        ->performedOn($this->logged_branch)
        ->log(':causer.firstname :causer.lastname has logged out from branch '.($this->logged_branch->branch->account->short_name ?? '').' - '.$this->logged_branch->branch->branch_name);

        Session::forget('logged_branch');
        Session::forget('coe_form_data');

        return redirect()->to('/home');
    }

    public function save_image($image_input, $id, $key) {
        // make directory if do not exist
        $dir = public_path().'/uploads/branch-login/'.$this->logged_branch->user_id.'/'.$id.'/'.$key;
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        chmod($dir, 0755);

        $image = Image::make($image_input);
        if($image->width() > $image->height()) { // landscape
            $image->widen(800)->save($dir.'/large.jpg'); // large
        } else { // portrait
            $image->heighten(700)->save($dir.'/large.jpg'); // large
        }
        $image = Image::make($image_input);
        $image->fit(100, 100)->save($dir.'/small.jpg'); // small
    }

    public function loadActivities() {
        $this->branch_activities = $this->logged_branch->login_activities()->whereNotNull('activity_id')->get();
    }

    public function mount($logged_branch) {
        $this->branch = $logged_branch->branch;
        $this->branch_activities = $logged_branch->login_activities()->whereNotNull('activity_id')->get();
    }

    public function render()
    {
        return view('livewire.accounts.account-branch-login-form');
    }
}
