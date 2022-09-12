<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use AccountLoginModel;

use Intervention\Image\Facades\Image;

use Livewire\WithFileUploads;

class AccountLoginForm extends Component
{
    use WithFileUploads;

    public $account, $accuracy, $longitude, $latitude, $activities, $picture_file;

    public function login() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'activities' => 'required',
            'picture_file' => 'image|max:2048',
        ]);

        $user = auth()->user();
        // check if logged in to other accounts
        $logged_account = AccountLoginModel::where('user_id', $user->id)
        ->whereNull('time_out')
        ->first();
        if(empty($logged_account)) { // new login 
            $login = new AccountLoginModel([
                'user_id' => $user->id,
                'account_id' => $this->account->id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'activities' => $this->activities,
                'time_in' => now(),
            ]);
            $login->save();

            $this->save_image($this->picture_file, $login->id);

        }
        
        return redirect()->to('/sales-order');
    }

    public function save_image($image_input, $id) {
        // make directory if do not exist
        $dir = public_path().'/uploads/account-login/'.auth()->user()->id.'/'.$id;
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

    public function render()
    {
        return view('livewire.accounts.account-login-form');
    }
}
