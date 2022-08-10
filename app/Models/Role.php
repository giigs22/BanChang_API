<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'users_roles');
    }
    public function templates()
    {
        return $this->belongsToMany(Template::class,'roles_templates');
    }
}
