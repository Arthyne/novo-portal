<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtratoMovimento extends Model
{
    protected $table = 'extrato_movimentos';
    public $timestamps = false;
    protected $fillable = ['portador_id', 'data_mov', 'descricao', 'valor', 'tipo', 'conciliado'];
}
