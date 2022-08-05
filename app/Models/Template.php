<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function widgets()
    {
        return $this->belongsToMany(Widget::class,'template_widget_relations');
    }
}
