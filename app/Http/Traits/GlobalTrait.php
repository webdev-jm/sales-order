<?php
     
namespace App\Http\Traits;
 
use App\Models\Setting;
 
trait GlobalTrait {
 
    public function getSettings() 
    {
        // Fetch all the settings from the 'settings' table.
        $setting = Setting::find(1);
        return $setting;
    }
}