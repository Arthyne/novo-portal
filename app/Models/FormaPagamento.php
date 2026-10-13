<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    protected $table = 'new_forma_pagamento';
    public $timestamps = false;
    protected $fillable = ['forma_pagamento', 'situacao'];
}
