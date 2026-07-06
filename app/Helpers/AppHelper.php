<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class AppHelper
{
    public function getAddress($lat, $long)
    {
        // Check if the request is secure
        $isSecure = request()->isSecure();

        if ($isSecure) {
            $address = $this->getAddressFromNominatim($lat, $long);

            if ($address === null) {
                $address = $this->getAddressFromOpenCage($lat, $long);
            }
        } else {
            $address = 'Cannot display address because the site is not secure.';
        }

        return $address;
    }

    protected function getAddressFromNominatim($lat, $long): ?string
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat='.trim($lat).'&lon='.trim($long).'&zoom=18&addressdetails=1';

        try {
            $response = Http::withHeaders(['User-Agent' => config('app.name').' ('.config('app.url').')'])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['display_name'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getAddressFromOpenCage($lat, $long): string
    {
        $url = 'https://api.opencagedata.com/geocode/v1/json?q='.trim($lat).'%2C'.trim($long).'&key=7712dad8e6924f52b9d9cd3d08f91122';

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                return $data['results'][0]['formatted'] ?? 'Failed to retrieve address.';
            }

            return 'Failed to retrieve address.';
        } catch (\Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }

    public static function instance()
    {
        return new AppHelper();
    }
}