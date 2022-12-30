<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DataExportController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 600);
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
    public function export_csv(Request $request)
    {
        $widget = $request->widget;
        $data = $request->data;
        $freq = $request->freq;
        $option = $request->option;

        if ($widget == 'env') {
            $device = Device::where('widget_id', 1)->get();
        }
        if($widget == 'smlight'){
            $device = Device::where('widget_id', 2)->get();
        }
        if($widget == 'smpole'){
            $device = Device::where('widget_id', 3)->get();
        }
        
        

        if ($freq == 'daily') {

            $list_data = [];
            foreach ($device as $key => $value) {
                $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $option['start'], $option['end'], null);
                $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                $get_location = $this->helpers->getLocation($get_attr);
                $get_status = $this->helpers->getStatus($get_attr);
                $get_avg = $this->AvgData($get_data);
                
                    $sdata['device'] = $value->device_name;
                    $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata['status'] = $get_status;
                    $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                    $list_data[] = $sdata;
                

            }
            return response()->json($list_data);

        }
        if ($freq == 'week' || $freq == 'month' || $freq == 'year') {
            $daysList = $this->helpers->setDayRange($option);
            $list_data = [];
            $f_list_data = [];
            $day_data = [];
            foreach ($device as $key => $value) {
                foreach ($daysList as $key2 => $date) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $get_status = $this->helpers->getStatus($get_attr);
                    $get_avg = $this->AvgData($get_data);

                    $sdata['device'] = $value->device_name;
                    $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata['status'] = $get_status;
                    $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                    $day_data[$key2] = $sdata;

                }
                $list_data[] = $day_data;
            }
            foreach ($list_data as $key3 => $value3) {
                $avg_week = $this->AvgDataWeek($value3, $data);
                
                    $sdata2['device'] = $value3[0]['device'];
                    $sdata2['location'] = $value3[0]['location'];
                    $sdata2['status'] = $value3[0]['status'];
                    $sdata2[$data] = ($avg_week == null) ? 0 : $avg_week;

                    $f_list_data[] = $sdata2;
                
            }
            return response()->json($f_list_data);

        }
        
    }
    public function AvgData($data)
    {
        $val = [];
        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $val[] = $value2->value;
            }
        }
        $collect = collect($val);
        $avg_data = $collect->avg();
        return $avg_data;
    }
    public function AvgDataWeek($list, $key)
    {
        $val = [];
        foreach ($list as $k => $value) {
            $val[] = $value[$key];
        }
        $collect = collect($val);
        $avg_data = $collect->avg();
        return $avg_data;
    }
}
