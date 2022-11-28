<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CCTVController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
    }
    public function responseData($type = 'last')
    {
        if ($type == 'attr') {
            $get_data = $this->api_helper->getAttrDataAPI($this->device);
        } else {
            $get_data = $this->api_helper->getLastDataAPI($this->device);
        }

        return $get_data;

    }
    public function responseStatus()
    {
        $get_attr = $this->api_helper->getAttrDataAPI($this->device);
        foreach ($get_attr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($value2->key == 'active') {
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
            $url_rtsp = $this->getRTSP($get_attr);

            $setdata['id'] = $value->id;
            $setdata['widget'] = 'cctv';
            $setdata['name'] = $value->location_name !== null ? $value->location_name : $value->device_name;
            $setdata['data'] = $get_data;
            $setdata['rtsp'] = $url_rtsp;
            $setdata['location'] = $get_location;
            $setdata['status'] = $get_status;
            $data[] = $setdata;
        }
        return $data;

    }
    public function getListDevice()
    {
        $list = Device::where('widget_id', 4)->get();
        return $list;
    }
    public function groupSur($data)
    {
        $keys = ['faceReg_alllist_daily', 'faceReg_blacklist_daily', 'tracking_daily', 'wrongDirection_daily', 'prohibitedArea_daily', 'prohibitedParking_daily', 'lpr_allplate_daily', 'lpr_blacklist_daily'];
        $group_data = [];
        foreach ($data as $key => $value) {
            $data2 = $value;
            foreach ($data2 as $key2 => $value2) {
                if (in_array($value2->key, $keys)) {
                    $group_data[$value2->key][] = $value2;
                }
            }
        }
        return $group_data;
    }
    public function getRTSP($data)
    {
        foreach ($data as $key => $value) {
            if ($value->key == 'rtsp') {
                return $value->value;
            }
        }
    }
    public function streaming(Request $request)
    {
        $url = "http://cctv.banchang.online/restreaming";
        $response = Http::post($url, ["url_rtsp" => $request->url_rtsp]);
        $data = json_decode($response);

        return $data;
    }

}
