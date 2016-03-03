<?php namespace Ttanai\Laramap;

use Ttanai\Laramap\Libraries\GoogleMaps;

class Laramap{
    function __construct(){
        $this->addresses = [];
        $this->splitSize = 8;
        $this->region = 'hu';
        $this->country = false;
    }

    function setCountry($country){
        $this->country = $country;
    }

    function setRegion($region){
        $this->region = $region;
    }

    function loadAddresses($addresses){
        $this->addresses = [];
        if ($this->country)
            foreach ($addresses as $address) {
                $this->addresses[] = $this->country . ', ' . $address;
            }
        else
            $this->addresses = $addresses;

        // split addresses for API
        $this->splittedAddresses = [];
        $firstPointer = 0;

        // split addresses
        while ($firstPointer < count($this->addresses)){
            $split = array_slice($this->addresses, $firstPointer, $this->splitSize);
            $firstPointer = $firstPointer + $this->splitSize - 1;

            $this->splittedAddresses[] = [
                                            'origin' => $split[0],
                                            'destination' => end($split),
                                            'waypoints' => array_slice($split, 1, count($split) - 2)
                                                ];
        }

    }

    function calculate(){
        $points['distances'] = [];
        // send segment to calculate
        foreach ($this->splittedAddresses as $splitCounter => $routePack) {
            $segmentDistances = $this->calculateRouteSegment($routePack);

            // wrong address
            if ($segmentDistances['status'] == 'ERROR'){
                foreach ($segmentDistances['errors'] as $errorLineInSegment) {
                    $errorLine = $splitCounter * $this->splitSize + $errorLineInSegment -1;
                    $points['errors'][$errorLine] = $this->addresses[ $errorLine ];
                }
                return $points;
            } else
                $points['distances'] = array_merge($points['distances'], $segmentDistances['distances']);
        }
        // summarize results
        foreach ($points['distances'] as $key => $point) {
            $points['distances'][$key]['original'] = [
                                                        'start_address' => $this->addresses[$key],
                                                        'end_address' => $this->addresses[$key+1]
                                                    ];
        }

        return $points;
    }


    function calculateRouteSegment( $routePack ){
        $result = GoogleMaps::getRouteFromGoogle( $routePack, $this->region );

        $route = new \SimpleXMLElement($result, false);

        if ($route->status != 'OK'){
            return $this->analyseError($route);
        }

        $points = ['status' => 'OK'];

        foreach ($route->route->leg as $leg) {
            $points['distances'][] = [
                            'duration_min' => round((int)$leg->duration->value / 60,2),
                            'distance_km' => round((string)$leg->distance->value / 1000,2),
                            'start_lat' => (string)$leg->start_location->lat,
                            'start_lng' => (string)$leg->start_location->lng,
                            'end_lat' => (string)$leg->end_location->lat,
                            'end_lng' => (string)$leg->end_location->lng,
                            'google' => [
                                'start_address' => (string)$leg->start_address,
                                'end_address' => (string)$leg->end_address
                            ],
                        ];
        }
        return $points;
    }

    function analyseError($route){
        $ret = ['status' => 'ERROR'];

        if ($route->status == 'NOT_FOUND'){
            for ($i = 0; $i < count($route->geocoded_waypoint); $i++){
                if ($route->geocoded_waypoint[$i]->geocoder_status != 'OK'){
                    $ret['errors'][] = $i;
                }
            }
        }
        return $ret;
    }

}
