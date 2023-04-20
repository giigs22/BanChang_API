<?php

namespace App\Http\Controllers\ResponseData;

use App\Classes\ApiHelper;
use App\Classes\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CCTVController extends Controller
{
    public function __construct()
    {
        $this->api_helper = new ApiHelper;
        $this->helpers = new Helpers;
        $this->device = $this->getListDevice();
    }
    public function responseData()
    {
        //if ($type == 'attr') {
         //   $get_data = $this->api_helper->getAttrDataAPI($this->device);
        //} else {
            $get_data = $this->api_helper->getLastDataAPI($this->device);
       // }

        return $get_data;

    }
    public function responseStatus()
    {
        $get_attr = $this->api_helper->getAttrDataAPI($this->device);
        foreach ($get_attr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($value2->key == 'active') {
                    $sdt[] = $value2;
                }
            }
        }
        $collect = collect($sdt);
        $group_status = $collect->countBy('value');
        $status = $this->helpers->statusDevice($group_status);
        return $status;
    }
    public function responseDataView()
    {
        $device = $this->device;
        $data = [];
        foreach ($device as $key => $value) {
            $get_attr = $this->api_helper->getAttrDataAPIByDevice($value->device_id);
            $get_data = $this->api_helper->getLastDataAPIByDevice($value->device_id);
            $get_location = $this->helpers->getLocation($get_attr);
            $get_status = $this->helpers->getStatus($get_attr);
            $url_rtsp = $this->getRTSP($get_attr);

            $setdata['id'] = $value->id;
            $setdata['widget'] = 'cctv';
            $setdata['name'] = $value->location_name !== null ? $value->location_name : $value->device_name;
            $setdata['data'] = $get_data;
            $setdata['rtsp'] = $url_rtsp;
            $setdata['location'] = $get_location;
            $setdata['status'] = $get_status;
            $data[] = $setdata;
        }
        return $data;

    }
    public function getListDevice()
    {
        $list = Device::where('widget_id', 4)->get();
        return $list;
    }
    public function groupSur($type)
    {
        $keys = ["face_recognition","camera_mulfunction","trespasser","suspected_face_detection","group_cluster_detection","traffic_violation","parking_violation"];
        
        if($type == "month"){
            $date_ts = $this->getDateMonth();
        }
        else{
            $date_ts = $this->getDateToday();
        }
        $group_data = [];
            foreach ($keys as $key) {
               $data =  $this->api_helper->getEventByKey($key,$date_ts[0],$date_ts[1]);
               $group_data[$key] = $data;
        }
        $sum_data = $this->sumGroup($group_data);
        return $sum_data;

    }
    public function getRTSP($data)
    {
        foreach ($data as $key => $value) {
            if ($value->key == 'rtsp') {
                return $value->value;
            }
        }
    }
    public function streaming(Request $request)
    {
        $url =  env("CCTV_URL_STREAMING");
        $response = Http::post($url, ["url_rtsp" => $request->url_rtsp]);
        $data = json_decode($response);

        return $data;
    }
    public function streaming_check(Request $request)
    {
        $url = $request->live_url;
        $response = Http::get($url);
        
        return $response->status();
    }
    public function getDateMonth()
    {
            $today = Carbon::today();
            $month = $today->month;
            $first = Carbon::create($today->year, $month, 1);
            $last = $today->endOfMonth()->format('Y-m-d');
            

            $start = $first->format('Y-m-d H:i:i');
            $end = $last." 23:59:59";


            $s_ts = Carbon::create($start)->valueOf();
            $e_ts = Carbon::create($end)->valueOf();

            return [$s_ts,$e_ts];
    }
    public function getDateToday()
    {
        $today = Carbon::today();
       
       

        $start = $today->format('Y-m-d H:i:i');
        $end = $today->format('Y-m-d')." 23:59:59";

        $s_ts = Carbon::create($start)->valueOf();
        $e_ts = Carbon::create($end)->valueOf();

         return [$s_ts,$e_ts];

    }
    public function sumGroup($data)
    {
        
        foreach ($data as $key => $value) {
            $sum = 0;
            foreach ($value as $key2 => $value2) {
                $sum += (int)$value2->event_count;
            }
            $dt[$key] = $sum; 
        }
        return $dt;
    }

}
