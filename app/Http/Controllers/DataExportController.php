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
        ini_set('max_execution_time', 0);
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
        if($widget == 'sos'){
            $device = Device::where('widget_id', 12)->get();
        }


        if ($freq == 'daily') {


            $list_group_data = [];
            if ($data == 'temp') {
                $data2 = 'temperature';
                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $option['start'], $option['end'], null);
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $get_status = $this->helpers->getStatus($get_attr);
                    if(array_key_exists('statusCode',(array) $get_data)){
                        $get_data = [];
                    }
                    $get_avg = $this->AvgData($get_data);

                    $sdata['device'] = $value->device_name;
                    $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata['status'] = $get_status;
                    $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                    $list_group_data[] = $sdata;

                    $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $option['start'], $option['end'], null);
                    if(array_key_exists('statusCode',(array) $get_data2)){
                        $get_data2 = [];
                    }
                    $get_avg2 = $this->AvgData($get_data2);

                    $sdata2['device'] = $value->device_name;
                    $sdata2['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata2['status'] = $get_status;
                    $sdata2[$data] = ($get_avg2 == null) ? 0 : $get_avg2;

                    $list_group_data[] = $sdata2;

                }
                $collect = collect($list_group_data);
                $group_avg = $collect->groupBy('device')->map(function($item,$key)use ($data){
                    $dt['device'] = $key;
                    $dt['location'] = $item[0]['location'];
                    $dt['status'] = $item[0]['status'];
                    $dt[$data] = $item->avg($data);
                    return $dt;
                });

                $list_data = [];
                foreach ($group_avg as $key => $value) {
                   $list_data[] = $value;
                }

            } else if ($data == 'humid') {
                $data2 = 'humidity';
                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $option['start'], $option['end'], null);
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $get_status = $this->helpers->getStatus($get_attr);
                    if(array_key_exists('statusCode',(array) $get_data)){
                        $get_data = [];
                    }
                    $get_avg = $this->AvgData($get_data);

                    $sdata['device'] = $value->device_name;
                    $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata['status'] = $get_status;
                    $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                    $list_group_data[] = $sdata;

                    $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $option['start'], $option['end'], null);
                    if(array_key_exists('statusCode',(array) $get_data2)){
                        $get_data2 = [];
                    }
                    $get_avg2 = $this->AvgData($get_data2);

                    $sdata2['device'] = $value->device_name;
                    $sdata2['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata2['status'] = $get_status;
                    $sdata2[$data] = ($get_avg2 == null) ? 0 : $get_avg2;

                    $list_group_data[] = $sdata2;

                }
                $collect = collect($list_group_data);
                $group_avg = $collect->groupBy('device')->map(function($item,$key)use ($data){
                    $dt['device'] = $key;
                    $dt['location'] = $item[0]['location'];
                    $dt['status'] = $item[0]['status'];
                    $dt[$data] = $item->avg($data);
                    return $dt;
                });

                $list_data = [];
                foreach ($group_avg as $key => $value) {
                   $list_data[] = $value;
                }
            }else if($data == 'all'){
                    $arr_data = ['co2','pm25','pm10','humid','uv','voc','temp','temperature','humidity'];
                    $list_data = [];

                        foreach ($device as $key => $value) {
                            foreach ($arr_data as $data) {
                            $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $option['start'], $option['end'], null);
                            $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                            $get_location = $this->helpers->getLocation($get_attr);
                            $get_status = $this->helpers->getStatus($get_attr);

                            if(array_key_exists('statusCode',(array) $get_data)){
                                $get_data = [];
                            }
                            $get_avg = $this->AvgData($get_data);

                            $dt['device'] = $value->device_name;
                            $dt['location'] = $get_location['lat'] . ',' . $get_location['long'];
                            $dt['status'] = $get_status;
                            if($data == 'temperature'){
                                $dt['temp'] = ($get_avg == null) ? 0 : $get_avg;
                            }else if($data == 'humidity'){
                                $dt['humid'] = ($get_avg == null) ? 0 : $get_avg;
                            }else{
                            $dt[$data] = ($get_avg == null) ? 0 : $get_avg;
                            }

                            }
                            $list_data[]  =$dt;
                        }

            }else{

                foreach ($device as $key => $value) {
                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $option['start'], $option['end'], null);
                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                    $get_location = $this->helpers->getLocation($get_attr);
                    $get_status = $this->helpers->getStatus($get_attr);

                    if(array_key_exists('statusCode',(array) $get_data)){
                        $get_data = [];
                    }

                    $get_avg = $this->AvgData($get_data);

                    $sdata['device'] = $value->device_name;
                    $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                    $sdata['status'] = $get_status;
                    $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                    $list_data[] = $sdata;

                }
            }

         return response()->json($list_data);

        }
        if ($freq == 'week' || $freq == 'month' || $freq == 'year') {
            $daysList = $this->helpers->setDayRange($option);
            $list_data = [];
            $f_list_data = [];
            $day_data = [];
            $list_group_data =[];

            if($data == 'temp'){
                $data2 = 'temperature';
                foreach ($device as $key => $value) {
                    foreach ($daysList as $key2 => $date) {
                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $date[0], $date[1], null);
                        if(array_key_exists('statusCode',(array) $get_data)){
                            $get_data = [];
                        }
                        if(array_key_exists('statusCode',(array) $get_data2)){
                            $get_data2 = [];
                        }

                        $get_avg = $this->AvgData($get_data);
                        $get_avg2 = $this->AvgData($get_data2);

                        $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                        $get_location = $this->helpers->getLocation($get_attr);
                        $get_status = $this->helpers->getStatus($get_attr);


                         $sdata['device'] = $value->device_name;
                         $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                         $sdata['status'] = $get_status;
                         $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                        $list_group_data[] = $sdata;



                         $sdata2['device'] = $value->device_name;
                         $sdata2['location'] = $get_location['lat'] . ',' . $get_location['long'];
                         $sdata2['status'] = $get_status;
                         $sdata2[$data] = ($get_avg2 == null) ? 0 : $get_avg2;

                        $list_group_data[] = $sdata2;

                        $collect = collect($list_group_data);
                        $group_avg = $collect->groupBy('device')->map(function($item,$key)use ($data){
                            $dt['device'] = $key;
                            $dt['location'] = $item[0]['location'];
                            $dt['status'] = $item[0]['status'];
                            $dt[$data] = $item->avg($data);
                            return $dt;
                        });

                        foreach ($group_avg as $key3 => $value3) {
                            $day_data[$key2] = $value3;
                        }

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

            }else if($data == 'humid'){
                $data2 = 'humidity';
                foreach ($device as $key => $value) {
                    foreach ($daysList as $key2 => $date) {
                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        $get_data2 = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data2, $date[0], $date[1], null);
                        if(array_key_exists('statusCode',(array) $get_data)){
                            $get_data = [];
                        }
                        if(array_key_exists('statusCode',(array) $get_data2)){
                            $get_data2 = [];
                        }

                        $get_avg = $this->AvgData($get_data);
                        $get_avg2 = $this->AvgData($get_data2);

                        $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                        $get_location = $this->helpers->getLocation($get_attr);
                        $get_status = $this->helpers->getStatus($get_attr);


                         $sdata['device'] = $value->device_name;
                         $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                         $sdata['status'] = $get_status;
                         $sdata[$data] = ($get_avg == null) ? 0 : $get_avg;

                        $list_group_data[] = $sdata;



                         $sdata2['device'] = $value->device_name;
                         $sdata2['location'] = $get_location['lat'] . ',' . $get_location['long'];
                         $sdata2['status'] = $get_status;
                         $sdata2[$data] = ($get_avg2 == null) ? 0 : $get_avg2;

                        $list_group_data[] = $sdata2;

                        $collect = collect($list_group_data);
                        $group_avg = $collect->groupBy('device')->map(function($item,$key)use ($data){
                            $dt['device'] = $key;
                            $dt['location'] = $item[0]['location'];
                            $dt['status'] = $item[0]['status'];
                            $dt[$data] = $item->avg($data);
                            return $dt;
                        });

                        foreach ($group_avg as $key3 => $value3) {
                            $day_data[$key2] = $value3;
                        }

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
            }else if($data == 'all'){
                $arr_data = ['co2','pm25','pm10','humid','uv','voc','temp','temperature','humidity'];

                        foreach ($device as $key => $value) {
                            foreach ($daysList as $key2 => $date) {
                                foreach ($arr_data as $data) {
                                    $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                                    $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                                    $get_location = $this->helpers->getLocation($get_attr);
                                    $get_status = $this->helpers->getStatus($get_attr);

                                    if(array_key_exists('statusCode',(array) $get_data)){
                                        $get_data = [];
                                    }

                                    $get_avg = $this->AvgData($get_data);

                                    $dt['device'] = $value->device_name;
                                    $dt['location'] = $get_location['lat'] . ',' . $get_location['long'];
                                    $dt['status'] = $get_status;
                                    if($data == 'temperature'){
                                        $dt['temp'] = ($get_avg == null) ? 0 : $get_avg;
                                    }else if($data == 'humidity'){
                                        $dt['humid'] = ($get_avg == null) ? 0 : $get_avg;
                                    }else{
                                        $dt[$data] = ($get_avg == null) ? 0 : $get_avg;
                                    }
                                }
                                $day_data[$key2] = $dt;
                            }
                            $list_data[] = $day_data;
                        }
                        foreach ($list_data as $key3 => $value3) {

                            $sdata2['device'] = $value3[0]['device'];
                            $sdata2['location'] = $value3[0]['location'];
                            $sdata2['status'] = $value3[0]['status'];

                            foreach ($arr_data as $data) {
                                if($data == 'temperature'){
                                    $data = 'temp';
                                }else if($data == 'humidity'){
                                    $data = 'humid';
                                }

                                $avg_week = $this->AvgDataWeek($value3, $data);
                                $sdata2[$data] = ($avg_week == null) ? 0 : $avg_week;
                            }




                            $f_list_data[] = $sdata2;

                        }
                        return response()->json($f_list_data);


            }else if($data == 'calls'){
                foreach ($device as $key => $value) {
                    foreach ($daysList as $key2 => $date) {
                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        if(array_key_exists('statusCode',(array) $get_data)){
                            $get_data = [];
                        }

                        $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
                        $get_location = $this->helpers->getLocation($get_attr);
                        $get_status = $this->helpers->getStatus($get_attr);
                        $get_sumpush = $this->sumpush($get_data);

                        $sdata['device'] = $value->device_name;
                        $sdata['location'] = $get_location['lat'] . ',' . $get_location['long'];
                        $sdata['status'] = $get_status;
                        $sdata[$data] = ($get_sumpush == null) ? 0 : $get_sumpush;

                        $day_data[$key2] = $sdata;

                    }
                    $list_data[] = $day_data;
                }
                foreach ($list_data as $key3 => $value3) {
                    $sum_week= $this->sumpushWeek($value3);

                        $sdata2['device'] = $value3[0]['device'];
                        $sdata2['location'] = $value3[0]['location'];
                        $sdata2['status'] = $value3[0]['status'];
                        $sdata2[$data] = ($sum_week == null) ? 0 : $sum_week;

                        $f_list_data[] = $sdata2;

                }
                return response()->json($f_list_data);


            }else{
                foreach ($device as $key => $value) {
                    foreach ($daysList as $key2 => $date) {
                        $get_data = $this->api_helper->getHistoryAPIByDevice($value->device_id, $data, $date[0], $date[1], null);
                        if(array_key_exists('statusCode',(array) $get_data)){
                            $get_data = [];
                        }

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

    }
    public function AvgData($data)
    {
        $val = [];
        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $val[] = (empty($value2->value))?0:$value2->value;
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
    public function sumpush($data)
    {
        $sum_val = 0;
        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $sum_val = $sum_val + (empty($value2->value))?0:count($value2->value);
            }
        }
        return $sum_val;
    }
    public function sumpushWeek($data)
    {
        $sum_val = 0;
        foreach ($data as $key => $value) {
              $sum_val = $sum_val+$value['calls'];

        }
        return $sum_val;
    }
}
