<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUnit extends Model
{
    protected $table = 'system_unit';
    public $timestamps = false;
    protected $fillable = ['name', 'connection_name'];
}
