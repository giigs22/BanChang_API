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
        $status = $this->helpers->statusDevice($get_attr);
        return $status;
    }
    public function getListDevice()
    {
        $list = Device::where('widget_id', 9)->get();
        return $list;
    }
}
