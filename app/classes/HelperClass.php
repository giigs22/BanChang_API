<?php
namespace App\Classes;

class Helpers
{
    public function statusDevice($data)
    {
        $online = 0;
        $offline = 0;

        foreach ($data as $key => $value) {
            if ($value[4]->key == 'active') {
                $val = $value[4]->value;
                if (empty($val)) {
                    $offline += 1;
                } else {
                    $online += 1;
                }
            } elseif ($value[6]->key == 'active') {
                $val = $value[6]->value;
                if (empty($val)) {
                    $offline += 1;
                } else {
                    $online += 1;
                }
            } elseif ($value[32]->key == 'active') {
                $val = $value[32]->value;
                if (empty($val)) {
                    $offline += 1;
                } else {
                    $online += 1;
                }
            } elseif ($value[34]->key == 'active') {
                $val = $value[34]->value;
                if (empty($val)) {
                    $offline += 1;
                } else {
                    $online += 1;
                }
            }
        }
        return ['online' => $online, 'offline' => $offline];
    }
    public function getStatus($data)
    {
        foreach ($data as $key => $value) {
            if ($value->key == 'active') {
                if ($value->value) {
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
