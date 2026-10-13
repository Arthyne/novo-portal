<?php

namespace App\Http\Controllers\Api;

use App\Models\ContaReceber;
use App\Models\MovimentoBancario;
use Illuminate\Http\Request;

class ContaReceberController extends BaseApiController
{
    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $query = ContaReceber::with(['pessoa', 'portador', 'categoria', 'centroCusto', 'formaPagamento'])
            ->where('empresa', $empresaId);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($de = $request->query('dt_vencimento_de')) {
            $query->whereDate('dt_vencimento', '>=', $de);
        }
        if ($ate = $request->query('dt_vencimento_ate')) {
            $query->whereDate('dt_vencimento', '<=', $ate);
        }
        if ($pessoaId = $request->query('pessoa_id')) {
            $query->where('pessoa_id', $pessoaId);
        }

        return response()->json(
            $query->orderBy('dt_vencimento', 'asc')->paginate(50)
        );
    }

    public function show(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $conta = ContaReceber::with(['pessoa', 'portador', 'categoria', 'centroCusto', 'formaPagamento'])
            ->where('empresa', $empresaId)
            ->findOrFail($id);

        return response()->json($conta);
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $dados = $request->all();
        $dados['empresa'] = $empresaId;
        $dados['status'] = $dados['status'] ?? 'A';

        $conta = ContaReceber::create($dados);

        return response()->json($conta, 201);
    }

    public function update(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $conta = ContaReceber::where('empresa', $empresaId)->findOrFail($id);
        $conta->update($request->all());

        return response()->json($conta);
    }

    public function destroy(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $conta = ContaReceber::where('empresa', $empresaId)->findOrFail($id);
        $conta->delete();

        return response()->json(['message' => 'Removido com sucesso']);
    }

    /**
     * Baixa (recebimento) de uma conta a receber e gera registro no movimento bancário.
     */
    public function baixar(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $conta = ContaReceber::where('empresa', $empresaId)->findOrFail($id);
        $conta->dt_pagamento = $request->input('dt_pagamento', now()->toDateString());
        $conta->juros = $request->input('juros', $conta->juros);
        $conta->multa = $request->input('multa', $conta->multa);
        $conta->status = 'B';
        $conta->save();

        // Evita duplicar o movimento caso a conta já tenha sido baixada antes
        $movExiste = MovimentoBancario::where('new_contasreceber_id', $conta->id)->exists();
        if (!$movExiste) {
            MovimentoBancario::create([
                'empresa'               => $conta->empresa,
                'status'                => 'B',
                'tipo'                  => 'R',
                'pessoa_id'             => $conta->pessoa_id,
                'valor'                 => $conta->valor,
                'dt_emissao'            => $conta->dt_emissao,
                'dt_vencimento'         => $conta->dt_vencimento,
                'dt_competencia'        => $conta->dt_competencia ?? $conta->dt_vencimento,
                'dt_pagamento'          => $conta->dt_pagamento,
                'obs'                   => $conta->obs,
                'new_forma_pagamento_id'=> $conta->new_forma_pagamento_id,
                'new_portador_id'       => $conta->new_portador_id,
                'new_categoria_id'      => $conta->new_categoria_id,
                'centro_custo_id'       => $conta->centro_custo_id,
                'new_contaspagar_id'    => null,
                'new_contasreceber_id'  => $conta->id,
            ]);
        }

        return response()->json($conta);
    }
}
