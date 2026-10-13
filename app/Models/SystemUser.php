<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUser extends Model
{
    protected $table = 'system_users';
    public $timestamps = false;
    protected $fillable = ['name', 'login', 'password', 'email', 'system_unit_id', 'active'];
    protected $hidden = ['password'];
}
