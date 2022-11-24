<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;

class WifiController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
    }
    public function responseData()
    {
        $get_data = $this->api_helper->getLastDataAPI($this->device);
        return $get_data;
    }
    public function responseStatus()
    {
        $get_attr = $this->api_helper->getAttrDataAPI($this->device);
        foreach ($get_attr as $key => $value) {
            foreach ($value as $key2 => $value2) {
               if($value2->key == 'active'){
                   $sdt[] = $value2;
               }
            }
       }
       $collect = collect($sdt);
       $group_status = $collect->countBy('value');
       $status = $this->helpers->statusDevice($group_status);

       return $status;
    }
    public function responseDataView()
    {
        $device = $this->device;
        $data = [];
        foreach ($device as $key => $value) {
            $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
            $get_data = $this->api_helper->getLastDataAPIByDevice($value->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);

            $setdata['id'] = $value->id;
            $setdata['widget'] = 'wifi';
            $setdata['name'] = $value->location_name !== null ? $value->location_name : $value->device_name;
            $setdata['data'] = $get_data;
            $setdata['location'] = $get_location;
            $setdata['status'] = $get_status;
            $data[] = $setdata;
        }
        return $data;

    }
    public function getListDevice()
    {
        $list = Device::where('widget_id', 9)->get();
        return $list;
    }
}
