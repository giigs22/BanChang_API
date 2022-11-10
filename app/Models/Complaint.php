<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    public function img_comp()
    {
        return $this->hasMany(ImgComplaint::class,'comp_id','id');
    }
}
