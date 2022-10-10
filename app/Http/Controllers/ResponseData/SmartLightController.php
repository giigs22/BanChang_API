<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;

class SmartLightController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
    }
    public function responseStatus()
    {
        $get_data = $this->api_helper->getLastDataAPI($this->device);

        $status = $this->status_lamp($get_data);
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
            $setdata['widget'] = 'smlight';
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
        $list = Device::where('widget_id', 2)->get();
        return $list;
    }
    public function status_lamp($data)
    {
        $online = 0;
        $offline = 0;

        foreach ($data as $key => $value) {
            if ($value->lamp[0]->value == "on") {
                $online += 1;
            } else {
                $offline += 1;
            }
        }
        return ['online' => $online, 'offline' => $offline];
    }
}
