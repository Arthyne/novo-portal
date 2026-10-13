<?php

namespace App\Http\Controllers\Api;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    public function resumo(Request $request)
    {
        $empresaId = $this->empresaId($request);
        $hoje = now()->toDateString();

        $pagarAbertoTotal = ContaPagar::where('empresa', $empresaId)
            ->where('status', 'A')
            ->sum('valor');

        $pagarVencidoTotal = ContaPagar::where('empresa', $empresaId)
            ->where('status', 'A')
            ->whereDate('dt_vencimento', '<', $hoje)
            ->sum('valor');

        $receberAbertoTotal = ContaReceber::where('empresa', $empresaId)
            ->where('status', 'A')
            ->sum('valor');

        $receberVencidoTotal = ContaReceber::where('empresa', $empresaId)
            ->where('status', 'A')
            ->whereDate('dt_vencimento', '<', $hoje)
            ->sum('valor');

        $pagarPago30d = ContaPagar::where('empresa', $empresaId)
            ->where('status', 'B')
            ->whereDate('dt_pagamento', '>=', now()->subDays(30)->toDateString())
            ->sum('valor');

        $receberRecebido30d = ContaReceber::where('empresa', $empresaId)
            ->where('status', 'B')
            ->whereDate('dt_pagamento', '>=', now()->subDays(30)->toDateString())
            ->sum('valor');

        return response()->json([
            'contas_pagar' => [
                'aberto' => (float) $pagarAbertoTotal,
                'vencido' => (float) $pagarVencidoTotal,
                'pago_30d' => (float) $pagarPago30d,
            ],
            'contas_receber' => [
                'aberto' => (float) $receberAbertoTotal,
                'vencido' => (float) $receberVencidoTotal,
                'recebido_30d' => (float) $receberRecebido30d,
            ],
            'saldo_previsto' => (float) ($receberAbertoTotal - $pagarAbertoTotal),
        ]);
    }

    public function fluxoCaixa(Request $request)
    {
        $empresaId = $this->empresaId($request);
        $meses = (int) $request->query('meses', 6);
        $inicio = now()->subMonths($meses)->startOfMonth()->toDateString();

        $pagar = ContaPagar::where('empresa', $empresaId)
            ->whereDate('dt_vencimento', '>=', $inicio)
            ->selectRaw("DATE_FORMAT(dt_vencimento, '%Y-%m') as mes, SUM(valor) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $receber = ContaReceber::where('empresa', $empresaId)
            ->whereDate('dt_vencimento', '>=', $inicio)
            ->selectRaw("DATE_FORMAT(dt_vencimento, '%Y-%m') as mes, SUM(valor) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json([
            'pagar' => $pagar,
            'receber' => $receber,
        ]);
    }
}
