<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class SOSController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
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
            $setdata['widget'] = 'sos';
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
        $list = Device::where('widget_id', 12)->get();
        return $list;
    }
    public function stat()
    {
        $device = $this->device;
        $data = [];
        foreach ($device as $key => $value) {
            $get_data = $this->api_helper->getLastDataAPIByDevice($value->device_id);

            $setdata['data'] = $get_data;
            $data[] = $setdata;
        }
        // foreach ($data as $key => $value) {
        //     $calls[] = $value['data']->calls;
        // }
        return $data;
    }
}
