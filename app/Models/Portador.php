<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portador extends Model
{
    protected $table = 'new_portador';
    public $timestamps = false;
    protected $fillable = ['portador', 'situacao', 'cod_contabil', 'cod_contabil_amz'];
}
