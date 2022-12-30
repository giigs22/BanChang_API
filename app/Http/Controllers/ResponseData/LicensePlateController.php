<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LicensePlateController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();

    }
    public function responseStat()
    {
       $url = 'http://ec2-13-251-222-89.ap-southeast-1.compute.amazonaws.com:3002/tenant/smartcity/ai/licence_plates';
       $response = Http::get($url);
       $data = json_decode($response);

        return $data;
    }
    public function responseData()
    {
        $get_data = $this->getLicensePlate($this->device);
        return $get_data;
    }
    public function getListDevice()
    {
        $list = Device::where('widget_id', 4)->get();
        return $list;
    }
    public function getLicensePlate($device)
    {
        $data = [];
        $list_license_plate=[];
        foreach ($device as $key => $value) {
            $get_data = $this->api_helper->getLastDataAPIByDevice($value->device_id);
            $data[] = $get_data;
        }
        foreach ($data as $key => $value) {
            if(isset($value->license_plate_recognition)){
            $list_license_plate[] = $value->license_plate_recognition->plate;
            }
        }
        return $list_license_plate;
    }
}
