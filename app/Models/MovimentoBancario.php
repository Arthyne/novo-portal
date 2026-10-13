<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimentoBancario extends Model
{
    protected $table = 'new_movbancario';

    protected $fillable = [
        'empresa', 'status', 'tipo', 'pessoa_id', 'valor',
        'dt_emissao', 'dt_vencimento', 'dt_competencia', 'dt_pagamento',
        'obs', 'new_forma_pagamento_id', 'new_portador_id', 'new_categoria_id',
        'centro_custo_id', 'new_contasreceber_id', 'new_contaspagar_id', 'caixa',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function portador()
    {
        return $this->belongsTo(Portador::class, 'new_portador_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'new_categoria_id');
    }

    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'new_forma_pagamento_id');
    }
}
