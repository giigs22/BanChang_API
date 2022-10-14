<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\ResponseData\CCTVController;
use App\Http\Controllers\ResponseData\EnvController;
use App\Http\Controllers\ResponseData\SmartLightController;
use App\Http\Controllers\ResponseData\SmartPoleController;
use App\Http\Controllers\ResponseData\WifiController;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DataController extends Controller
{
    public function get_data(Request $request, $type, $sensor, $option = null)
    {
        if ($type == 'lastdata') {
            if ($sensor == 'env') {
                $envCon = new EnvController;
                if ($option == 'view') {
                    $data = $envCon->responseDataView();
                } else {
                    $data = $envCon->responseData();
                }
            } elseif ($sensor == 'smart_pole') {
                $smpCon = new SmartPoleController;
                if ($option == 'view') {
                    $data = $smpCon->responseDataView();
                } else {
                    $data = $smpCon->responseData();
                }
            } elseif ($sensor == 'wifi') {
                $wfCon = new WifiController;
                $data = $wfCon->responseData();
            } elseif ($sensor == 'cctv_sur') {
                $cctvCon = new CCTVController;
                if ($option == 'chartdata') {
                    $setdata = $cctvCon->responseData('attr');
                    $data = $cctvCon->groupSur($setdata);
                }
            } elseif ($sensor == 'smart_light') {
                $smCon = new SmartLightController;
                if ($option == 'view') {
                    $data = $smCon->responseDataView();
                }
            } elseif ($sensor == 'cctv') {
                $cctvCon = new CCTVController;
                if ($option == 'view') {
                    $data = $cctvCon->responseDataView();
                }
            }
        }

        return $data;

    }
    public function get_status(Request $request, $sensor)
    {
        $status = $this->getDeviceStatus($sensor);
        return $status;

    }
    public function getDeviceStatus($sensor)
    {
        if ($sensor == 'cctv') {
            $cctvCon = new CCTVController;
            $data = $cctvCon->responseStatus();
        } elseif ($sensor == 'smart_light') {
            $smCon = new SmartLightController;
            $data = $smCon->responseStatus();
        } elseif ($sensor == 'smart_pole') {
            $smpCon = new SmartPoleController;
            $data = $smpCon->responseStatus();
        } elseif ($sensor == 'wifi') {
            $wfCon = new WifiController;
            $data = $wfCon->responseStatus();
        }
        return $data;
    }
    public function device_all()
    {
        $list_device = Device::all();
        $api_helper = new ApiHelper;
        $helpers = new Helpers;
        $list_status = $api_helper->getAttrDataAPI($list_device);
        $status = $helpers->statusDevice($list_status);

        return $status;

    }
}
