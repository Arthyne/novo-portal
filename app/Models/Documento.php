<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';
    public $timestamps = false;
    protected $fillable = ['empresa_id', 'nome_arquivo', 'tipo', 'tamanho', 'caminho', 'usuario_id', 'data_upload'];
}
