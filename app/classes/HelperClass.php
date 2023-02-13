<?php
namespace App\Classes;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;

class Helpers
{
    public function statusDevice($data)
    {
        $online = 0;
        $offline = 0;

        if(isset($data['1'])){
            $online = $data['1'];
        }
        if(isset($data['0'])){
            $offline = $data['0'];
        }
        if(isset($data['True'])){
            $online = $online + $data['True'];
        }
        if(isset($data['False'])){
            $offline = $offline + $data['False'];
        }
        
        return ['online' => $online, 'offline' => $offline];
    }
    public function getStatus($data)
    {
        
        foreach ($data as $key => $value) {
            if ($value->key == 'active') {
                 if ($value->value || $value->value == "True") {
                     $status = 1;
                 }
                 if($value->value == false || $value->value == "False") {
                     $status = 0;
                 }
            }
        }
        return $status;
    }
    public function getLocation($data)
    {
        $location = [];
        foreach ($data as $key => $value) {
            if ($value->key == "latitude" || $value->key == "lat") {
                $location['lat'] = (float) $value->value;
            } elseif ($value->key == "longitude" || $value->key == "long") {
                $location['long'] = (float) $value->value;
            }
        }
        return $location;
    }
    public function setDayHourRange($data)
    {
        $con_ts = Carbon::createFromTimestampMs($data['start'])->toDateString();
        $list = [];
        
        for ($i=0; $i < 24; $i++) {
            $start = Carbon::parse($con_ts.'00:00')->toDateTimeString(); 
            $d = Carbon::create($start);
            $s = $d->addHours($i)->toDateTimeString();
            $e = Carbon::parse($s)->addMinutes(59)->toDateTimeString();
            $list[] = [Carbon::parse($s)->valueOf(),Carbon::parse($e)->valueOf()]; 
        }
        return $list;
    }
    public function setDayRange($option)
    {
        $start = $option['start'];
        $end = $option['end'];

        $parse_start = Carbon::createFromTimestampMs($start);
        $parse_end = Carbon::createFromTimestampMs($end);

        $setdate = CarbonPeriod::create($parse_start, $parse_end);
        foreach ($setdate as $date) {
            $date_format = Carbon::create($date);
            $start = $date_format->startOfDay()->toDateTimeString();
            $end = $date_format->endOfDay()->toDateTimeString();
            $ts_start = Carbon::create($start)->valueOf();
            $ts_end = Carbon::create($end)->valueOf();
            $date_list[] = [$ts_start, $ts_end];
        }
        return $date_list;
    }
    public function AvgMultiArray($data)
    {
        $val = [];
        foreach ($data as $key => $value) {
            $val[] = $value->value;
        }

        $collect = collect($val);
        $avg_data = $collect->avg();
        return $avg_data;
    }
    public function AvgArray($arr)
    {
        $collect = collect($arr);
        $avg_data = $collect->avg();
        return $avg_data;
    }
    public function widgetKey($id)
    {
        if ($id == '1') {
            $key = 'aqi';
        } elseif ($id == '2') {
            $key = 'smlight';
        } elseif ($id == '3') {
            $key = 'smpole';
        } elseif ($id == '4') {
            $key = 'cctv';
        } elseif ($id == '9') {
            $key = 'wifi';
        } elseif ($id == '12') {
            $key = 'sos';
        }
        return $key;
    }
    
}
