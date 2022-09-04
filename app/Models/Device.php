<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    public function widget()
    {
        return $this->hasOne(Widget::class, 'id', 'widget_id');
    }
    public function backup()
    {
        return $this->hasOne(Backup::class,'device_id','id');
    }
    public function location()
    {
        return $this->hasOne(Location::class,'device_id','id');
    }
}
