<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'empresa_id', 'tipo', 'natureza', 'num_documento', 'serie', 'pessoa_id',
        'chave_acesso', 'dt_emissao', 'dt_competencia', 'valor_total',
        'descricao', 'observacao', 'status',
        'arquivo_path', 'arquivo_nome', 'xml_path', 'usuario_id',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class, 'documento_id');
    }

    public function contasReceber()
    {
        return $this->hasMany(ContaReceber::class, 'documento_id');
    }
}
