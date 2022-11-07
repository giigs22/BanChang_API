<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\ResponseData\CCTVController;
use App\Http\Controllers\ResponseData\DigitalSignageController;
use App\Http\Controllers\ResponseData\EnvController;
use App\Http\Controllers\ResponseData\SmartLightController;
use App\Http\Controllers\ResponseData\SmartPoleController;
use App\Http\Controllers\ResponseData\SOSController;
use App\Http\Controllers\ResponseData\WifiController;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DataController extends Controller
{
    public function get_data($type, $sensor, $option = null)
    {
        $envCon = new EnvController;
        $smpCon = new SmartPoleController;
        $wfCon = new WifiController;
        $cctvCon = new CCTVController;
        $smCon = new SmartLightController;
        $cctvCon = new CCTVController;
        $digi = new DigitalSignageController;
        $sos = new SOSController;

        if ($type == 'lastdata') {
            if ($sensor == 'env') {
                if ($option == 'view') {
                    $data = $envCon->responseDataView();
                } else {
                    $data = $envCon->responseData();
                }
            } elseif ($sensor == 'smart_pole') {
                if ($option == 'view') {
                    $data = $smpCon->responseDataView();
                } else {
                    $data = $smpCon->responseData();
                }
            } elseif ($sensor == 'wifi') {
                if ($option == 'view') {
                    $data = $wfCon->responseDataView();
                } else {
                    $data = $wfCon->responseData();
                }
            } elseif ($sensor == 'cctv_sur') {
                if ($option == 'chartdata') {
                    $setdata = $cctvCon->responseData('attr');
                    $data = $cctvCon->groupSur($setdata);
                }
            } elseif ($sensor == 'smart_light') {
                if ($option == 'view') {
                    $data = $smCon->responseDataView();
                }
            } elseif ($sensor == 'cctv') {
                if ($option == 'view') {
                    $data = $cctvCon->responseDataView();
                }
            } elseif ($sensor == 'digi_sig') {
                if ($option == 'view') {
                    $data = $digi->responseDataView();
                }
            } elseif ($sensor == 'sos') {
                if ($option == 'view') {
                    $data = $sos->responseDataView();
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
