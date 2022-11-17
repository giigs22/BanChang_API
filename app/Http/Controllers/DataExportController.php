<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use Illuminate\Http\Request;

class DataExportController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
    public function export_csv(Request $request)
    {
        $widget = $request->widget;
        $data = $request->data;
        $freq = $request->freq;
        $option = $request->option;

        if($widget == 'env'){
            $device = Device::where('widget_id',1)->get();
        }

        if($freq == 'daily'){
            $list_data = [];
            foreach ($device as $key => $value) {
                $list_data[] = $this->api_helper->getHistoryAPIByDevice($value->device_id,$data,$option['start'],$option['end'],null);
            }
            
        }


    }
}
