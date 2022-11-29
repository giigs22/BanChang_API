<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;

class ChartDataController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 600);
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
    public function get_data(Request $request)
    {
        $widget = $request->widget;
        $data = $request->data;
        $freq = $request->freq;
        $option = $request->option;

        if ($widget == 'env') {
            $device = Device::where('widget_id', 1)->get();
        }

        if($freq == 'daily'){
            $day = $this->getday($option);
            $list_data = [];
            $list_data2 = [];
            foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $day[0], $day[1], null);
                    if(!empty($get_data->$data)){
                        $list_data[] = $get_data;
                    }
            }

            foreach ($list_data as $key2 => $value2) {
                    $list_data2[] = $value2->$data;
            }
           
        }
    }
    public function getday($data)
    {
        $start = Carbon::createFromTimestampMs($data['start'], config('app.timezone'));
        
        $s = $start->startOfDay()->valueOf();
        $e = $start->endOfDay()->valueOf();

        // $pre = CarbonInterval::hours(1)->toPeriod($s,$e);

        // foreach ($pre as $key => $value) {
        //     $list_time[] = $value->valueOf();
        // }
        return [$s,$e];

    }
}
