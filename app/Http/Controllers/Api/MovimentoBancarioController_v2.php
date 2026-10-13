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
            'dt_emissao'           => $m->dt_emissao,
            'dt_competencia'       => $m->dt_competencia,
            'dt_vencimento'        => $m->dt_vencimento,
            'obs'                  => $m->obs,
            'pessoa_id'            => $m->pessoa_id,
            'new_portador_id'      => $m->new_portador_id,
            'new_categoria_id'     => $m->new_categoria_id,
            'centro_custo_id'      => $m->centro_custo_id,
            'new_forma_pagamento_id' => $m->new_forma_pagamento_id,
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

    public function store(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $dados = $request->all();
        $dados['empresa'] = $empresaId;
        $dados['status']  = $dados['status'] ?? 'A';
        $dados['caixa']   = $dados['caixa']  ?? 'R';

        // Para novos registros: dt_vencimento, dt_emissao e dt_competencia = dt_pagamento (mesma lógica do onSave original)
        $dtPag = $dados['dt_pagamento'] ?? null;
        if ($dtPag) {
            $dados['dt_vencimento']  = $dados['dt_vencimento']  ?: $dtPag;
            $dados['dt_emissao']     = $dados['dt_emissao']     ?: $dtPag;
            $dados['dt_competencia'] = $dados['dt_competencia'] ?: $dtPag;
        }

        // Garante que FK vazias virem null
        foreach (['pessoa_id','new_forma_pagamento_id','new_portador_id','new_categoria_id','centro_custo_id','new_contaspagar_id','new_contasreceber_id'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        $movimento = MovimentoBancario::create($dados);

        return response()->json($movimento, 201);
    }

    public function update(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $movimento = MovimentoBancario::where('empresa', $empresaId)->findOrFail($id);

        $dados = $request->all();

        foreach (['pessoa_id','new_forma_pagamento_id','new_portador_id','new_categoria_id','centro_custo_id','new_contaspagar_id','new_contasreceber_id'] as $campo) {
            if (isset($dados[$campo]) && $dados[$campo] === '') {
                $dados[$campo] = null;
            }
        }

        $movimento->update($dados);

        return response()->json($movimento);
    }

    public function destroy(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $movimento = MovimentoBancario::where('empresa', $empresaId)->findOrFail($id);
        $movimento->delete();

        return response()->json(['message' => 'Movimento excluído com sucesso.']);
    }
}
