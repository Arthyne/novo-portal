<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroCusto extends Model
{
    protected $table = 'new_centro_custo';
    public $timestamps = false;
    protected $fillable = ['cc', 'ativo', 'cod_contabil', 'cod_contabil_amz'];
}
