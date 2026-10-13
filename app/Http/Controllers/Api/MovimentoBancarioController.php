<?php

namespace App\Http\Controllers\Api;

use App\Models\MovimentoBancario;
use Illuminate\Http\Request;

class MovimentoBancarioController extends BaseApiController
{
    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $query = MovimentoBancario::with(['pessoa', 'portador', 'categoria', 'centroCusto', 'formaPagamento'])
            ->where('empresa', $empresaId);

        if ($de = $request->query('pagamento_de')) {
            $query->whereDate('dt_pagamento', '>=', $de);
        }
        if ($ate = $request->query('pagamento_ate')) {
            $query->whereDate('dt_pagamento', '<=', $ate);
        }
        if ($de = $request->query('competencia_de')) {
            $query->whereDate('dt_competencia', '>=', $de);
        }
        if ($ate = $request->query('competencia_ate')) {
            $query->whereDate('dt_competencia', '<=', $ate);
        }
        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }
        if ($valor = $request->query('valor')) {
            $query->where('valor', $valor);
        }
        if ($pessoaId = $request->query('pessoa_id')) {
            $query->where('pessoa_id', $pessoaId);
        }
        if ($formaId = $request->query('new_forma_pagamento_id')) {
            $query->where('new_forma_pagamento_id', $formaId);
        }
        if ($portadorId = $request->query('new_portador_id')) {
            $query->where('new_portador_id', $portadorId);
        }
        if ($categoriaId = $request->query('new_categoria_id')) {
            $query->where('new_categoria_id', $categoriaId);
        }
        if ($centroId = $request->query('centro_custo_id')) {
            $query->where('centro_custo_id', $centroId);
        }
        if ($obs = $request->query('obs')) {
            $query->where('obs', 'like', "%{$obs}%");
        }

        $movimentos = $query->orderBy('dt_pagamento', 'desc')->get()->map(fn($m) => [
            'id'                   => $m->id,
            'tipo'                 => $m->tipo,
            'valor'                => $m->valor,
            'dt_pagamento'         => $m->dt_pagamento,
            'dt_competencia'       => $m->dt_competencia,
            'obs'                  => $m->obs,
            'new_contaspagar_id'   => $m->new_contaspagar_id,
            'new_contasreceber_id' => $m->new_contasreceber_id,
            'pessoa_nome'          => $m->pessoa?->nome_reduzido ?? $m->pessoa?->nome ?? null,
            'cpf_cnpj'             => $m->pessoa?->cpf_cnpj ?? null,
            'portador'             => $m->portador?->portador ?? null,
            'forma_pagamento'      => $m->formaPagamento?->forma_pagamento ?? null,
            'categoria'            => $m->categoria?->categoria ?? null,
            'centro_custo'         => $m->centroCusto?->cc ?? null,
        ]);

        return response()->json($movimentos);
    }
}
