<?php

namespace App\Http\Controllers\Api;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Documento;
use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends BaseApiController
{
    // ─── Listagem ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $query = Documento::with('pessoa')
            ->where('empresa_id', $empresaId);

        if ($tipo = $request->query('tipo'))     $query->where('tipo', $tipo);
        if ($status = $request->query('status')) $query->where('status', $status);
        if ($de  = $request->query('emissao_de'))  $query->whereDate('dt_emissao', '>=', $de);
        if ($ate = $request->query('emissao_ate'))  $query->whereDate('dt_emissao', '<=', $ate);

        $docs = $query->orderByDesc('dt_emissao')->get();

        // Conta vínculos
        $docs->each(function ($doc) {
            $doc->n_pagar   = $doc->contasPagar()->count();
            $doc->n_receber = $doc->contasReceber()->count();
        });

        return response()->json($docs);
    }

    // ─── Cadastro manual ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $dados = $request->except(['arquivo', 'xml']);
        $dados['empresa_id'] = $empresaId;
        $dados['status']     = $dados['status'] ?? 'ATIVO';

        // Upload PDF
        if ($request->hasFile('arquivo')) {
            $path = $request->file('arquivo')->store("documentos/{$empresaId}", 'public');
            $dados['arquivo_path'] = $path;
            $dados['arquivo_nome'] = $request->file('arquivo')->getClientOriginalName();
        }

        $doc = Documento::create($dados);

        return response()->json($doc->load('pessoa'), 201);
    }

    public function update(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);
        $doc = Documento::where('empresa_id', $empresaId)->findOrFail($id);

        $dados = $request->except(['arquivo', 'xml']);

        if ($request->hasFile('arquivo')) {
            if ($doc->arquivo_path) Storage::disk('public')->delete($doc->arquivo_path);
            $path = $request->file('arquivo')->store("documentos/{$empresaId}", 'public');
            $dados['arquivo_path'] = $path;
            $dados['arquivo_nome'] = $request->file('arquivo')->getClientOriginalName();
        }

        $doc->update($dados);

        return response()->json($doc->load('pessoa'));
    }

    public function destroy(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);
        $doc = Documento::where('empresa_id', $empresaId)->findOrFail($id);

        if ($doc->arquivo_path) Storage::disk('public')->delete($doc->arquivo_path);
        if ($doc->xml_path)     Storage::disk('public')->delete($doc->xml_path);

        $doc->delete();

        return response()->json(['ok' => true]);
    }

    // ─── Vínculos ────────────────────────────────────────────────────────────

    public function vinculos(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);
        $doc = Documento::where('empresa_id', $empresaId)->findOrFail($id);

        return response()->json([
            'pagar'   => $doc->contasPagar()->with('pessoa')->get(),
            'receber' => $doc->contasReceber()->with('pessoa')->get(),
        ]);
    }

    /** Vincula um documento a uma conta a pagar ou receber existente */
    public function vincular(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);
        $doc = Documento::where('empresa_id', $empresaId)->findOrFail($id);

        $tipo    = $request->input('tipo');   // 'pagar' ou 'receber'
        $contaId = $request->input('conta_id');

        if ($tipo === 'pagar') {
            $conta = ContaPagar::where('empresa', $empresaId)->findOrFail($contaId);
            $conta->update(['documento_id' => $doc->id]);
        } elseif ($tipo === 'receber') {
            $conta = ContaReceber::where('empresa', $empresaId)->findOrFail($contaId);
            $conta->update(['documento_id' => $doc->id]);
        } else {
            return response()->json(['error' => 'tipo deve ser pagar ou receber'], 422);
        }

        return response()->json(['ok' => true]);
    }

    // ─── Importação de XML NF-e ──────────────────────────────────────────────

    public function importarXml(Request $request)
    {
        $empresaId = $this->empresaId($request);

        if (!$request->hasFile('xml')) {
            return response()->json(['error' => 'Arquivo XML não enviado'], 422);
        }

        $xmlFile    = $request->file('xml');
        $xmlContent = file_get_contents($xmlFile->getRealPath());

        // Parse do XML
        $dados = $this->parseNFe($xmlContent);
        if (!$dados) {
            return response()->json(['error' => 'XML inválido ou não reconhecido como NF-e'], 422);
        }

        // Salva o arquivo XML
        $xmlPath = $xmlFile->store("documentos/{$empresaId}/xml", 'public');

        // Busca ou cria a pessoa pelo CNPJ do emitente
        $pessoaId = null;
        if (!empty($dados['cnpj_emit'])) {
            $cnpjLimpo = preg_replace('/\D/', '', $dados['cnpj_emit']);
            $pessoa = Pessoa::where('cpf_cnpj', 'like', "%{$cnpjLimpo}%")->first();
            $pessoaId = $pessoa?->id;
        }

        // Cria o documento
        $doc = Documento::create([
            'empresa_id'     => $empresaId,
            'tipo'           => 'NF',
            'num_documento'  => $dados['num_nf'],
            'serie'          => $dados['serie'],
            'pessoa_id'      => $pessoaId,
            'chave_acesso'   => $dados['chave_acesso'],
            'dt_emissao'     => $dados['dt_emissao'],
            'valor_total'    => $dados['valor_total'],
            'descricao'      => "NF-e {$dados['num_nf']} — {$dados['nome_emit']}",
            'status'         => 'ATIVO',
            'xml_path'       => $xmlPath,
            'arquivo_nome'   => $xmlFile->getClientOriginalName(),
        ]);

        // Gera contas a pagar automaticamente
        $parcelas      = $dados['parcelas'];
        $totalParcelas = count($parcelas);
        $contasCriadas = [];

        if ($totalParcelas === 0) {
            // Sem duplicatas no XML — cria 1 parcela com vencimento = emissão
            $parcelas = [[
                'vencimento' => $dados['dt_emissao'],
                'valor'      => $dados['valor_total'],
            ]];
            $totalParcelas = 1;
        }

        foreach ($parcelas as $i => $parcela) {
            $numParcela = $i + 1;
            $obs = $totalParcelas > 1
                ? "Parcela {$numParcela} de {$totalParcelas} — NF {$dados['num_nf']}"
                : "NF {$dados['num_nf']} — {$dados['nome_emit']}";

            $venc = $parcela['vencimento'];
            [$ano, $mes] = [substr($venc, 0, 4), substr($venc, 5, 2)];

            $conta = ContaPagar::create([
                'empresa'         => $empresaId,
                'documento_id'    => $doc->id,
                'pessoa_id'       => $pessoaId,
                'valor'           => $parcela['valor'],
                'parcela'         => "{$numParcela}/{$totalParcelas}",
                'dt_emissao'      => $dados['dt_emissao'],
                'dt_vencimento'   => $venc,
                'dt_competencia'  => $dados['dt_emissao'],
                'num_documento'   => $dados['num_nf'],
                'obs'             => $obs,
                'status'          => 'A',
                'tipo_documento'  => 'NF',
                'mes_vencimento'  => (int)$mes,
                'ano_vencimento'  => (int)$ano,
                'ano_mes_vencimento' => (int)($ano . $mes),
                'mes_emissao'     => (int)substr($dados['dt_emissao'], 5, 2),
                'ano_emissao'     => (int)substr($dados['dt_emissao'], 0, 4),
                'ano_mes_emissao' => (int)(substr($dados['dt_emissao'], 0, 4) . substr($dados['dt_emissao'], 5, 2)),
            ]);

            $contasCriadas[] = $conta;
        }

        return response()->json([
            'documento'    => $doc->load('pessoa'),
            'contas_pagar' => $contasCriadas,
            'parcelas'     => $totalParcelas,
        ], 201);
    }

    // ─── Parser NF-e ─────────────────────────────────────────────────────────

    private function parseNFe(string $xmlContent): ?array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);
        if (!$xml) return null;

        // Registra namespace NF-e
        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

        // Tenta encontrar infNFe com ou sem namespace
        $infNFe = $xml->NFe->infNFe ?? $xml->xpath('//nfe:infNFe')[0] ?? null;
        if (!$infNFe) return null;

        $ide  = $infNFe->ide;
        $emit = $infNFe->emit;
        $tot  = $infNFe->total->ICMSTot ?? null;

        // Chave de acesso (Id="NFe...44dígitos")
        $idAttr      = (string)($infNFe->attributes()['Id'] ?? '');
        $chaveAcesso = preg_replace('/\D/', '', $idAttr);
        if (strlen($chaveAcesso) !== 44) $chaveAcesso = null;

        // Data de emissão
        $dhEmi   = (string)($ide->dhEmi ?? $ide->dEmi ?? '');
        $dtEmissao = substr($dhEmi, 0, 10); // pega só YYYY-MM-DD

        // Parcelas (duplicatas)
        $parcelas = [];
        if (isset($infNFe->cobr->dup)) {
            foreach ($infNFe->cobr->dup as $dup) {
                $venc = (string)$dup->dVenc;
                // Converte DD/MM/YYYY para YYYY-MM-DD se necessário
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $venc)) {
                    [$d, $m, $y] = explode('/', $venc);
                    $venc = "{$y}-{$m}-{$d}";
                }
                $parcelas[] = [
                    'vencimento' => $venc,
                    'valor'      => (float)$dup->vDup,
                ];
            }
        }

        return [
            'num_nf'      => (string)$ide->nNF,
            'serie'       => (string)$ide->serie,
            'chave_acesso'=> $chaveAcesso,
            'dt_emissao'  => $dtEmissao,
            'valor_total' => $tot ? (float)$tot->vNF : 0,
            'nome_emit'   => (string)($emit->xNome ?? ''),
            'cnpj_emit'   => (string)($emit->CNPJ ?? $emit->CPF ?? ''),
            'parcelas'    => $parcelas,
        ];
    }
}
