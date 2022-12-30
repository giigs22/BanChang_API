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

        if ($freq == 'daily') {
            $day = $this->helpers->setDayHourRange($option);
            $list_data = [];

            if ($data == 'temp') {
                $data2 = 'temperature';

                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $day[0], $day[23], null);
                    if (!empty($get_data->$data)) {
                        $list_data[] = $get_data;
                    }
                    $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $day[0], $day[23], null);
                    if (!empty($get_data2->$data2)) {
                        $list_data[] = $get_data2;
                    }
                }

                $list_data2 = $this->getKey($list_data, $data, $data2);
                $group = $this->mergeArr($list_data2);
                $sort_data = $this->avgData($group, $day);
                 //set data for chart
                 $sdata = [];
                 $slabel = [];
                if(!empty($sort_data)){
                    $set_date_data = $this->groupHour($day, $sort_data);
                    foreach ($set_date_data as $key => $value) {
                        $sdata[] = round($this->helpers->AvgArray($value), 2);
                        $slabel[] = $key;
                    }
                }
                
                return response()->json([$slabel, $sdata]);

            } else if ($data == 'humid') {
                $data2 = 'humidity';

                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $day[0], $day[23], null);
                    if (!empty($get_data->$data)) {
                        $list_data[] = $get_data;
                    }
                    $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $day[0], $day[23], null);
                    if (!empty($get_data2->$data2)) {
                        $list_data[] = $get_data2;
                    }
                }
                $list_data2 = $this->getKey($list_data, $data, $data2);
                $group = $this->mergeArr($list_data2);
                $sort_data = $this->avgData($group, $day);
                
                 //set data for chart
                 $sdata = [];
                 $slabel = [];
                if(!empty($sort_data)){
                    $set_date_data = $this->groupHour($day, $sort_data);
                    foreach ($set_date_data as $key => $value) {
                        $sdata[] = round($this->helpers->AvgArray($value), 2);
                        $slabel[] = $key;
                    }
                }
                return response()->json([$slabel, $sdata]);

            } else {
                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $day[0], $day[23], null);
                    if (!empty($get_data->$data)) {
                        $list_data[] = $get_data;
                    }
                }

                $list_data2 = $this->getKey($list_data, $data);
                $group = $this->mergeArr($list_data2);
                $sort_data = $this->avgData($group, $day);
                $set_date_data = $this->groupHour($day, $sort_data);

                //set data for chart
                $sdata = [];
                $slabel = [];

                foreach ($set_date_data as $key => $value) {
                    $sdata[] = round($this->helpers->AvgArray($value), 2);
                    $slabel[] = $key;
                }
                return response()->json([$slabel, $sdata]);
            }
        }elseif($freq == 'week'){
            $day_range = $this->helpers->setDayRange($option);
            if($data == 'temp'){
                $data = 'temperature';
            }
            if($data == 'humid'){
                $data = 'humidity';
            }
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
            if($data == 'temp'){
                $data = 'temperature';
            }
            if($data == 'humid'){
                $data = 'humidity';
            }
            $start = Carbon::createFromTimestampMs($option['start'])->toDateString();
            $end = Carbon::createFromTimestampMs($option['end'])->toDateString();
            
            $data = Stat::where('type_data',$data)->whereBetween('date',[$start,$end])->orderBy('date')->get();
            
            $sdata = [];
            $slabel = [];
            foreach ($data as $key => $value) {
                $sdata[] = $value->avg_value;
                $slabel[] = substr($value->date,8,2);
            }
            return response()->json([$slabel, $sdata]);
        }
    }
    public function getKey($list_data, $keydata, $keydata2 = null)
    {
        //get data in key
        $list_data2 = [];
        foreach ($list_data as $key => $value) {
            if (isset($value->$keydata)) {
                $list_data2[] = $value->$keydata;
            }
        }
        if (!empty($keydata2)) {
            foreach ($list_data as $key => $value) {
                if (isset($value->$keydata2)) {
                    $list_data2[] = $value->$keydata2;
                }
            }
        }
        return $list_data2;

    }
    public function mergeArr($list_data)
    {
        //merge all array
        $merge_arr = [];
        foreach ($list_data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $merge_arr[] = $value2;
            }
        }
        $collect = collect($merge_arr);
        $group = $collect->groupBy('ts')->sortBy('ts');

        return $group;
    }
    public function avgData($group, $day)
    {
        //avg data
        $avg_arr = [];
        $set = [];
        foreach ($group as $key => $value) {
            $date_data = Carbon::createFromTimestampMs($key)->toDateString();
            $date_now = Carbon::createFromTimestampMs($day[0])->toDateString();
            $diff = Carbon::create($date_data)->diffInDays($date_now);
            //if ($diff == 0) {
                $set['date_time'] = Carbon::createFromTimestampMs($key)->toDateTimeString();
                $set['value'] = $this->helpers->AvgMultiArray($value);
            //}
            $avg_arr[] = $set;
        }
        $collect2 = collect($avg_arr);
        //sort time asc
        $sort_data = $collect2->sortBy('date_time');
        return $sort_data;
    }
    public function groupHour($day, $sort_data)
    {
        //group value to hour
        $set_date_data = [];
        foreach ($day as $value) {
            $date = Carbon::createFromTimestampMs($value)->toDateTimeString();

            foreach ($sort_data as $key2 => $value2) {
                $time1 = Carbon::create($date)->format('H:i');
                $time2 = Carbon::create($value2['date_time'])->format('H:i');
                $same_hour = Carbon::create($time1)->isSameHour($time2);

                if ($same_hour) {
                    $set_date_data[$time1][] = $value2['value'];
                }
            }

        }
        return $set_date_data;
    }

}
