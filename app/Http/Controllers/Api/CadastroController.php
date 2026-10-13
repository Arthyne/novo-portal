<?php

namespace App\Http\Controllers\Api;

use App\Models\CentroCusto;
use App\Models\Categoria;
use App\Models\FormaPagamento;
use App\Models\Pessoa;
use App\Models\Portador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CadastroController extends BaseApiController
{
    private function modelFor(string $recurso)
    {
        return match ($recurso) {
            'portadores' => Portador::class,
            'categorias' => Categoria::class,
            'centros', 'centros-custo' => CentroCusto::class,
            'formas', 'formas-pagamento' => FormaPagamento::class,
            'pessoas' => Pessoa::class,
            default => abort(404, 'Recurso inválido'),
        };
    }

    private function scoped(Request $request, string $recurso, string $model)
    {
        return $model::query();
    }

    public function index(Request $request, string $recurso)
    {
        $model = $this->modelFor($recurso);
        $query = $model::query();

        if ($busca = $request->query('busca')) {
            $campo = match ($recurso) {
                'portadores' => 'portador',
                'categorias' => 'categoria',
                'centros', 'centros-custo' => 'cc',
                'formas', 'formas-pagamento' => 'forma_pagamento',
                'pessoas' => 'nome',
                default => null,
            };
            if ($campo) {
                $query->where($campo, 'like', "%{$busca}%");
            }
        }

        $ordem = match ($recurso) {
            'portadores' => 'portador',
            'categorias' => 'mestre_categoria',
            'centros', 'centros-custo' => 'cc',
            'formas', 'formas-pagamento' => 'forma_pagamento',
            default => 'nome',
        };

        return response()->json($query->orderBy($ordem)->get());
    }

    public function show(Request $request, string $recurso, int $id)
    {
        $model = $this->modelFor($recurso);
        $registro = $this->scoped($request, $recurso, $model)->findOrFail($id);

        return response()->json($registro);
    }

    public function store(Request $request, string $recurso)
    {
        $model = $this->modelFor($recurso);
        $dados = $request->all();

        if ($recurso === 'pessoas') {
            $dados['empresa'] = $this->empresaId($request);

            if (!empty($dados['cpf_cnpj'])) {
                $docLimpo = preg_replace('/\D/', '', $dados['cpf_cnpj']);
                $existe = $model::whereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(cpf_cnpj,'.',''),'-',''),'/',''),' ','') = ?",
                    [$docLimpo]
                )->exists();

                if ($existe) {
                    return response()->json([
                        'message' => 'Já existe um cadastro com este CPF/CNPJ.',
                    ], 422);
                }
            }
        }

        $registro = $model::create($dados);

        return response()->json($registro, 201);
    }

    public function update(Request $request, string $recurso, int $id)
    {
        $model = $this->modelFor($recurso);
        $registro = $this->scoped($request, $recurso, $model)->findOrFail($id);

        if ($recurso === 'pessoas' && !empty($request->cpf_cnpj)) {
            $docLimpo = preg_replace('/\D/', '', $request->cpf_cnpj);
            $existe = $model::whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(cpf_cnpj,'.',''),'-',''),'/',''),' ','') = ?",
                [$docLimpo]
            )->where('id', '!=', $id)->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Já existe um cadastro com este CPF/CNPJ.',
                ], 422);
            }
        }

        $registro->update($request->all());

        return response()->json($registro);
    }

    public function destroy(Request $request, string $recurso, int $id)
    {
        $model = $this->modelFor($recurso);
        $registro = $this->scoped($request, $recurso, $model)->findOrFail($id);

        $coluna = match ($recurso) {
            'portadores' => 'new_portador_id',
            'categorias' => 'new_categoria_id',
            'centros', 'centros-custo' => 'centro_custo_id',
            'formas', 'formas-pagamento' => 'new_forma_pagamento_id',
            'pessoas' => 'pessoa_id',
        };
        foreach (['new_contaspagar', 'new_contasreceber', 'new_movbancario'] as $tabela) {
            if (DB::table($tabela)->where($coluna, $id)->exists()) {
                return response()->json([
                    'message' => 'Este cadastro já é usado em lançamentos e não pode ser excluído. Desative-o em vez de excluir.',
                ], 422);
            }
        }

        $registro->delete();

        return response()->json(['message' => 'Removido com sucesso']);
    }
}
