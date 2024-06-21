<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AppHelper
{
    public function getAddress($lat, $long)
    {
        // $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat='.trim($lat).'&lon='.trim($long).'&zoom=18&addressdetails=1';

        $url = 'https://api.opencagedata.com/geocode/v1/json?q='.trim($lat).'%2C'.trim($long).'&key=7712dad8e6924f52b9d9cd3d08f91122';

        // Check if the request is secure
        $isSecure = request()->isSecure();

        if ($isSecure) {
            try {
                $response = Http::get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['results'][0]['formatted'];
                    // $address = $data['display_name'];
                } else {
                    $address = 'Failed to retrieve address.';
                }
            } catch (\Exception $e) {
                $address = 'An error occurred: ' . $e->getMessage();
            }
        } else {
            $address = 'Cannot display address because the site is not secure.';
        }

        return $address;
    }

    public static function instance()
    {
        return new AppHelper();
    }
}