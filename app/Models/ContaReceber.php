<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
    protected $table = 'new_contasreceber';
    public $timestamps = false;
    protected $fillable = [
        'empresa', 'documento_id', 'status', 'pessoa_id', 'valor', 'parcela', 'dt_emissao',
        'dt_vencimento', 'dt_competencia', 'dt_pagamento', 'num_documento', 'codbarras', 'obs',
        'new_forma_pagamento_id', 'new_portador_id', 'new_categoria_id', 'centro_custo_id',
        'juros', 'multa', 'caixa', 'tipo_documento',
        'mes_vencimento', 'ano_vencimento', 'ano_mes_vencimento', 'mes_emissao', 'ano_emissao',
        'ano_mes_emissao', 'mes_pagamento', 'ano_pagamento', 'ano_mes_pagamento',
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
