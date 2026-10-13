<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUserUnit extends Model
{
    protected $table = 'system_user_unit';
    public $timestamps = false;
    protected $fillable = ['system_user_id', 'system_unit_id'];
}
