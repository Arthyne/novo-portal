<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'new_categoria';
    public $timestamps = false;
    protected $fillable = ['mestre_categoria', 'categoria', 'tipo', 'situacao', 'ordem_mestrecategoria', 'ordem_categoria', 'cod_contabil', 'cod_contabil_amz'];
}
