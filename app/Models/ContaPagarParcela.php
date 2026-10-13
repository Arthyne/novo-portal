<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaPagarParcela extends Model
{
    protected $table = 'contas_pagar_parcelas';
    public $timestamps = false;
    protected $fillable = ['conta_id', 'numero', 'valor', 'vencimento', 'status', 'data_pagamento'];
}
