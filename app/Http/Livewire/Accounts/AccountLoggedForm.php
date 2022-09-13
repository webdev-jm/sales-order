<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;

use Livewire\WithFileUploads;

use Intervention\Image\Facades\Image;

class AccountLoggedForm extends Component
{
    use WithFileUploads;
    
    public $logged, $account, $accuracy, $longitude, $latitude, $activities, $picture_file;
    public $image_url;

    public function logout() {
        $this->validate([
            // 'accuracy' => 'required',
            // 'longitude' => 'required',
            // 'latitude' => 'required',
            'activities' => 'required',
            'picture_file' => 'image|max:2048',
        ]);

        $this->logged->update([
            // 'longitude' => $this->longitude,
            // 'latitude' => $this->latitude,
            // 'accuracy' => $this->accuracy,
            'activities' => $this->activities,
            'time_out' => now(),
        ]);

        if($this->picture_file) {
            $this->save_image($this->picture_file, $this->logged->id);
        }

        return redirect()->to('/home');
    }

    public function save_image($image_input, $id) {
        // make directory if do not exist
        $dir = public_path().'/uploads/account-login/'.$this->logged->user_id.'/'.$id;
        if(!is_dir($dir)) {
            mkdir($dir, 755, true);
        }

        $image = Image::make($image_input);
        if($image->width() > $image->height()) { // landscape
            $image->widen(800)->save($dir.'/large.jpg'); // large
        } else { // portrait
            $image->heighten(700)->save($dir.'/large.jpg'); // large
        }
        $image = Image::make($image_input);
        $image->fit(100, 100)->save($dir.'/small.jpg'); // small
    }

    public function mount() {
        $this->account = Account::findOrFail($this->logged->account_id);
        $this->accuracy = $this->logged->accuracy;
        $this->longitude = $this->logged->longitude;
        $this->latitude = $this->logged->latitude;
        $this->activities = $this->logged->activities;
        $this->image_url = public_path().'/uploads/account-login/'.$this->logged->user_id.'/'.$this->logged->id;
    }

    public function render()
    {
        return view('livewire.accounts.account-logged-form');
    }
}
