<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;

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

        $status = $this->helpers->statusDevice($get_attr);
        return $status;
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

}