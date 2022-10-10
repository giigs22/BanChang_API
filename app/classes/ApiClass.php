<?php
namespace App\Classes;

use Illuminate\Support\Facades\Http;

class ApiHelper
{
    public $api_url;
    public $api_last;
    public $api_attr;

    public function __construct()
    {
        $this->api_url = env("API_DATA_URL");
        $this->api_last = env("API_LAST_DATA");
        $this->api_attr = env("API_ATTR_DATA");
    }

    public function getLastDataAPI($device)
    {
        $data = [];
        foreach ($device as $key => $value) {
            $end_point = str_replace('{device_id}', $value->device_id, $this->api_last);
            $url_full = $this->getFullUrl($end_point);

            $response = Http::get($url_full);
            $data[] = json_decode($response);
        }
        return $data;
    }
    public function getAttrDataAPI($device)
    {
        $data = [];
        foreach ($device as $key => $value) {
            $end_point = str_replace('{device_id}', $value->device_id, $this->api_attr);
            $url_full = $this->getFullUrl($end_point);

            $response = Http::get($url_full);
            $data[] = json_decode($response);
        }
        return $data;
    }
    public function getAttrDataAPIByDevice($device)
    {
        $end_point = str_replace('{device_id}', $device, $this->api_attr);
        $url_full = $this->getFullUrl($end_point);

        $response = Http::get($url_full);
        $data = json_decode($response);

        return $data;
    }
    public function getLastDataAPIByDevice($device)
    {
        $end_point = str_replace('{device_id}', $device, $this->api_last);
        $url_full = $this->getFullUrl($end_point);

        $response = Http::get($url_full);
        $data = json_decode($response);

        return $data;
    }
    public function getFullUrl($end_point)
    {
        return $this->api_url . $end_point;
    }
}
