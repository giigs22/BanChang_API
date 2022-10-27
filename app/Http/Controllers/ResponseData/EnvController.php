<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Support\Facades\Http;

class EnvController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
    }
    public function responseData()
    {

        $type_sensor = $this->setTypeEnv($this->device);
        $lnr = $type_sensor[0];
        $env = $type_sensor[1];

        $get_data_lnr = $this->api_helper->getLastDataAPI($lnr);
        $get_data_env = $this->api_helper->getLastDataAPI($env);

        $list_lnr = [];
        $list_env = [];
        foreach ($get_data_lnr as $key => $value) {
            if(!empty($value)){
                $list_lnr[] = $value;
            }
        }
        foreach ($get_data_env as $key => $value) {
            if(!empty($value)){
                $list_env[] = $value;
            }
        }
        return ['lnr' => $list_lnr, 'env' => $list_env];

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
            $setdata['widget'] = 'aqi';
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
        $list = Device::where('widget_id', 1)->get();
        return $list;
    }
    public function setTypeEnv($list_device)
    {
        $lnr_sensor = [];
        $env_sensor = [];

        foreach ($list_device as $key => $value) {
            if ($value->type == 'LNR') {
                $lnr_sensor[] = $value;
            } else {
                $env_sensor[] = $value;
            }
        }
        return [$lnr_sensor, $env_sensor];
    }
}
