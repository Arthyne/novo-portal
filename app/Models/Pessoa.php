<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    protected $table = 'pessoa';
    public $timestamps = false;
    protected $fillable = [
        'empresa', 'cli_for', 'nome', 'nome_reduz', 'cpf_cnpj', 'fisico_juridico',
        'insc_estadual', 'insc_municipal', 'endereco', 'numero', 'complemento',
        'bairro', 'cidade_id', 'nome_cidade', 'uf', 'cep', 'pais', 'telefone',
        'celular', 'email', 'dadospagamento', 'proprietario',
    ];
}
