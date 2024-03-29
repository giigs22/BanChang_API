<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\ResponseData\CCTVController;
use App\Http\Controllers\ResponseData\DigitalSignageController;
use App\Http\Controllers\ResponseData\EnvController;
use App\Http\Controllers\ResponseData\LicensePlateController;
use App\Http\Controllers\ResponseData\SmartLightController;
use App\Http\Controllers\ResponseData\SmartPoleController;
use App\Http\Controllers\ResponseData\SOSController;
use App\Http\Controllers\ResponseData\WifiController;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DataController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 0);
    }
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
        $license = new LicensePlateController;

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
                    $data = $cctvCon->groupSur('today'); 
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
            }elseif($sensor == 'check_license_plate'){
                if($option == 'stat')
                $data = $license->responseStat();
            }elseif($sensor == 'license_plate'){
                $data = $license->responseData();
            }
        }
        if($type == "history"){
            if ($sensor == 'cctv_sur') {
                if ($option == 'chartdata') {
                    $data = $cctvCon->groupSur('month'); 
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
        }elseif($sensor == 'digi_sig'){
            $digi = new DigitalSignageController;
            $data = $digi->responseStatus();
        }
        return $data;
    }
    public function device_all()
    {
        $list_device = Device::all();
        $api_helper = new ApiHelper;
        $helpers = new Helpers;
        $list_attr = $api_helper->getAttrDataAPI($list_device);

         foreach ($list_attr as $key => $value) {
             foreach ($value as $key2 => $value2) {
                if($value2->key == 'active'){
                    $sdt[] = $value2;
                }
             }
        }
        $collect = collect($sdt);
        $group_status = $collect->countBy('value');
        $status = $helpers->statusDevice($group_status);
        
        return $status;

    }
}
