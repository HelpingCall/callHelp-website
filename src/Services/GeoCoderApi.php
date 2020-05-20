<?php

namespace App\Services;

class GeoCoderApi
{
    /**
     * @var string
     */
    private $APIKey;

    public function __construct()
    {
        $this->APIKey = $_ENV['GEOCODER_API_KEY'];
    }

    public function reversGeocoding(string $lat, string $long): string
    {
        $geocoder = new \OpenCage\Geocoder\Geocoder($this->APIKey);
        try {
            $result = $geocoder->geocode($lat.','.$long);
        } catch (\Exception $e) {
        } // latitude,longitude (y,x)

        return $result['results'][0]['formatted'];
    }
}
