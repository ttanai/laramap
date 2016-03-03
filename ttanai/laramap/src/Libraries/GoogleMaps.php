<?php namespace Ttanai\Laramap\Libraries;

class GoogleMaps{
    public static function getRouteFromGoogle($routePack, $region){
        $url = 'https://maps.googleapis.com/maps/api/directions/xml?origin=' . urlencode($routePack['origin']) .
                        '&destination=' . urlencode($routePack['destination']) .
                        '&waypoints=' . urlencode(implode('|', $routePack['waypoints'])) .
                        '&region=' . $region . '&language=en' .
                        '&mode=driving' .
                        '&key=' . env('LARAMAP_GOOGLE_API_KEY');

        $defaults = array(
            CURLOPT_POST => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 10
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($defaults));
        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }


}
