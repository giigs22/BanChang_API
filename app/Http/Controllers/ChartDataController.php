<?php

namespace App\Http\Controllers;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Models\Device;
use App\Models\Stat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartDataController extends Controller
{
    public function __construct()
    {
        ini_set('max_execution_time', 0);
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

        if ($freq == 'daily') {
            $day = $this->helpers->setDayHourRange($option);
            $list_data = [];

            if ($data == 'temp') {
                $data2 = 'temperature';

                foreach ($device as $key => $value) {
                    foreach ($day as $date) {
                        $time = Carbon::createFromTimestampMs($date[0])->format('H:i');

                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        $list_data[$time][] = $get_data;

                        $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $date[0], $date[1], null);
                        $list_data[$time][] = $get_data2;
                    }
                   
                }

                 $list_data2 = $this->getKey($list_data, $data,$data2);
                 $merge_data = $this->mergeArr($list_data2,$option['start']);
                 $avg_data = $this->avgData($merge_data);

                 //set data for chart
                 $sdata = [];
                 $slabel = [];

                foreach ($avg_data as $key => $value) {
                    $sdata[] = round($value['value'], 2);
                    $slabel[] = $value['date_time'];
                }
                return response()->json([$slabel, $sdata]);

            } else if ($data == 'humid') {
                $data2 = 'humidity';

                foreach ($device as $key => $value) {
                    foreach ($day as $date) {
                        $time = Carbon::createFromTimestampMs($date[0])->format('H:i');

                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        $list_data[$time][] = $get_data;

                        $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $date[0], $date[1], null);
                        $list_data[$time][] = $get_data2;
                    }
                   
                }

                 $list_data2 = $this->getKey($list_data, $data,$data2);
                 $merge_data = $this->mergeArr($list_data2,$option['start']);
                 $avg_data = $this->avgData($merge_data);

                 //set data for chart
                 $sdata = [];
                 $slabel = [];

                foreach ($avg_data as $key => $value) {
                    $sdata[] = round($value['value'], 2);
                    $slabel[] = $value['date_time'];
                }
                return response()->json([$slabel, $sdata]);

            } else {
                foreach ($device as $key => $value) {
                    foreach ($day as $date) {
                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        $time = Carbon::createFromTimestampMs($date[0])->format('H:i');
                        $list_data[$time][] = $get_data;
                    }
                
                }
                  $list_data2 = $this->getKey($list_data, $data);
                  $merge_data = $this->mergeArr($list_data2,$option['start']);
                  $avg_data = $this->avgData($merge_data);

               
                 //set data for chart
                 $sdata = [];
                 $slabel = [];

                foreach ($avg_data as $key => $value) {
                    $sdata[] = round($value['value'], 2);
                    $slabel[] = $value['date_time'];
                }
                return response()->json([$slabel, $sdata]);
            
            }
        }elseif($freq == 'week'){
            $day_range = $this->helpers->setDayRange($option);
           
            $list_data = [];
            foreach ($day_range as $date) {
                $con_date = Carbon::createFromTimestampMs($date[0])->toDateString();
                $get_data = Stat::where(['date'=>$con_date,'type_data'=>$data])->first();
                $dt['date_time'] = $con_date;
                if(!empty($get_data)){
                $dt['value'] = $get_data->avg_value;
                }else{
                $dt['value'] = 0;
                }
                $list_data[] = $dt;
            }
            $col_list = collect($list_data);
            $set_date_data = $col_list->sortBy('date_time');

            $sdata = [];
            $slabel = [];
            
            foreach ($set_date_data as $key => $value) {
                $sdata[] = $value['value'];
                $slabel[] = $value['date_time'];
            }
            return response()->json([$slabel, $sdata]);
            
        }elseif($freq== 'month'){
           
            // $start = Carbon::createFromTimestampMs($option['start'])->toDateString();
            // $end = Carbon::createFromTimestampMs($option['end'])->toDateString();
            
            // $data = Stat::where('type_data',$data)->whereBetween('date',[$start,$end])->orderBy('date')->get();
            
            // $sdata = [];
            // $slabel = [];
            // foreach ($data as $key => $value) {
            //     $sdata[] = $value->avg_value;
            //     $slabel[] = substr($value->date,8,2);
            // }
            //return response()->json([$slabel, $sdata]);

            $day_range = $this->helpers->setDayRange($option);
           
            $list_data = [];
            foreach ($day_range as $date) {
                $con_date = Carbon::createFromTimestampMs($date[0])->toDateString();
                $get_data = Stat::where(['date'=>$con_date,'type_data'=>$data])->first();
                $dt['date_time'] = $con_date;
                if(!empty($get_data)){
                $dt['value'] = $get_data->avg_value;
                }else{
                $dt['value'] = 0;
                }
                $list_data[] = $dt;
            }
            $col_list = collect($list_data);
            $set_date_data = $col_list->sortBy('date_time');

            $sdata = [];
            $slabel = [];
            
            foreach ($set_date_data as $key => $value) {
                $sdata[] = $value['value'];
                $slabel[] = $value['date_time'];
            }
            return response()->json([$slabel, $sdata]);
        }
    }
    public function getKey($list_data, $keydata, $keydata2 = null)
    {
        //get data in key
        $list_data2 = [];
        foreach ($list_data as $key => $value) {
            foreach ($value as $key2 => $value2) {
             if (isset($value2->$keydata)) {
                 $list_data2[$key][] = $value2->$keydata;
             }
            }
        }
        if (!empty($keydata2)) {
            foreach ($list_data as $key => $value) {
                foreach ($value as $key2 => $value2) {
                if (isset($value2->$keydata2)) {
                    $list_data2[$key][] = $value2->$keydata2;
                }
                }
            }
        }
        return $list_data2;

    }
    public function mergeArr($list_data,$ts_start)
    {
        //merge all array
         $merge_arr = [];
         foreach ($list_data as $key => $value) {
            $sdata = [];
                 foreach ($value as $key2 => $value2) {
                    if(!empty($value2)){
                        foreach ($value2 as $key3 => $value3) {
                            if($value3->ts > $ts_start){
                            $sdata[] = $value3;
                            }
                        }
                    }
                 }
            $merge_arr[$key] = $sdata;
         }
        return $merge_arr;   
    }
    public function avgData($merge_data)
    {
        //avg data
        $avg_arr = [];
        foreach ($merge_data as $key => $value) {
            $set['date_time'] = $key;
            $set['value'] = $this->helpers->AvgMultiArray($value);
            $avg_arr[] = $set;
        }
        return $avg_arr;
    }
   

}
