<?php
namespace App\Helpers;

class AppHelper
{
    public function getAddress($lat,$lng)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
        $json = @file_get_contents($url);
        $data=json_decode($json);
        $status = $data->status;

        if($status=="OK")
        {
            return $data->results[0]->formatted_address;
        } else {
            return $data->error_message;
        }
    }

    public static function instance()
    {
        return new AppHelper();
    }
}