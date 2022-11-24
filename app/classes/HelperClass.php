<?php
namespace App\Classes;

class Helpers
{
    public function statusDevice($data)
    {
        $online = 0;
        $offline = 0;

        if(isset($data['True'])){
            $online = $data['True'];
        }
        if(isset($data['0'])){
            $offline = $data['0'];
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
                if ($value->value || $value->value == 'True') {
                    return 1;
                } else {
                    return 0;
                }
            }
        }
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
}
