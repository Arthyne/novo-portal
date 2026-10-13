<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovBancario extends Model
{
    protected $table = 'new_movbancario';
    public $timestamps = false;
    protected $fillable = [
        'empresa', 'status', 'tipo', 'pessoa_id', 'valor', 'dt_emissao', 'dt_vencimento',
        'dt_competencia', 'dt_pagamento', 'obs', 'new_forma_pagamento_id', 'new_portador_id',
        'new_categoria_id', 'centro_custo_id', 'new_contasreceber_id', 'new_contaspagar_id', 'caixa',
    ];
}
