<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AppHelper
{
    public function getAddress($lat,$long)
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat='.trim($lat).'&lon='.trim($long).'&zoom=18&addressdetails=1';

        if(request()->secure()) {
            $response = Http::get($url);
            $address = $response['display_name'];
        } else {
            $address = 'cannot display address because the site is not secure.';
        }

        return $address;
    }

    public static function instance()
    {
        return new AppHelper();
    }
}