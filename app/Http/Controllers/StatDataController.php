<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use App\Models\Stat;
use Carbon\Carbon;

class StatDataController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 900);
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
    }
    public function getData($type)
    {
        if($type == 'pm25' || $type == 'temp' || $type == 'humid' || $type == 'uv' || $type = 'voc'){
            $device = Device::where('widget_id', '1')->get();
        }

        $yesterday = Carbon::yesterday();
        $y_start = Carbon::parse($yesterday)->startOfDay()->valueOf();
        $y_end = Carbon::parse($yesterday->toDateString().'23:59:59')->valueOf();
        
        $data = $type;
        $list_data = [];

        if ($data == 'temp') {
            $data2 = 'temperature';
            foreach ($device as $key => $value) {
                $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data,$y_start,$y_end, null);
                if(!empty($get_data->$data)){
                    $list_data[] = $get_data->$data;
                }    
            }
            foreach ($device as $key => $value) {
                $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2,$y_start,$y_end, null);
                if(!empty($get_data2->$data2)){
                    $list_data[] = $get_data2->$data2;
                }    
            }
            //return $list_data;
            $setdata = [];
            foreach($list_data as $key => $value){
                foreach ($value as $key2 => $value2) {
                    $setdata[] = $value2->value;
                }
            }
            $avg = $this->helpers->AvgArray($setdata);

        } elseif ($data == 'humid') {
            $data2 = 'humidity';
            foreach ($device as $key => $value) {
                $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data,$y_start,$y_end, null);
                if(!empty($get_data->$data)){
                    $list_data[] = $get_data->$data;
                }    
            }
            foreach ($device as $key => $value) {
                $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2,$y_start,$y_end, null);
                if(!empty($get_data2->$data2)){
                    $list_data[] = $get_data2->$data2;
                }    
            }
            //return $list_data;
            $setdata = [];
            foreach($list_data as $key => $value){
                foreach ($value as $key2 => $value2) {
                    $setdata[] = $value2->value;
                }
            }
            $avg = $this->helpers->AvgArray($setdata);

        }else{
            foreach ($device as $key => $value) {
                $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data,$y_start,$y_end, null);
                if(!empty($get_data->$data)){
                    $list_data[] = $get_data->$data;
                }    
            }
        
            //return $list_data;
            $setdata = [];
            foreach($list_data as $key => $value){
                foreach ($value as $key2 => $value2) {
                    $setdata[] = $value2->value;
                }
            }
            $avg = $this->helpers->AvgArray($setdata);
        }
           

            $add = new Stat();
            $add->date = $yesterday->toDateString();
            $add->type_data = $data;
            $add->avg_value = $avg;
            $add->save();

            if($add){
                return response()->json(['success' => true]);
            }
            
        
    }
    
    public function setDate()
    {
        $day = Carbon::create();
        $start = $day->startOfDay()->valueOf();
        $end = $day->endOfDay()->valueOf();

        return [$start, $end];
    }
    public function setData($list_data)
    {
        $set_data = [];
        foreach ($list_data as $key => $value) {
            $date = Carbon::createFromTimestampMs($key)->toDateString();
            $avg = $this->helpers->AvgMultiArray($value) == null ? 0 : $this->helpers->AvgMultiArray($value);
            $set_data[$date] = $avg;
        }
        return $set_data;
    }
    public function setData2($set_data, $range)
    {
        $set_data2 = [];
        foreach ($range as $date) {
            $cdate = Carbon::createFromTimestampMs($date[0])->toDateString();
            foreach ($set_data as $key => $value) {
                if (array_key_exists($cdate, $set_data)) {
                    $set_data2[$key] = $value;
                } else {
                    $set_data2[$cdate] = 0;
                }
            }
        }
        return $set_data2;
    }
}
