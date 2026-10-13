<?php

namespace App\Http\Controllers\Api;

use App\Models\MovBancario;
use Illuminate\Http\Request;

class ConciliacaoController extends BaseApiController
{
    public function index(Request $request)
    {
        $empresaId = $this->empresaId($request);

        $query = MovBancario::where('empresa', $empresaId);

        if ($portadorId = $request->query('portador_id')) {
            $query->where('new_portador_id', $portadorId);
        }
        if ($de = $request->query('data_de')) {
            $query->whereDate('dt_pagamento', '>=', $de);
        }
        if ($ate = $request->query('data_ate')) {
            $query->whereDate('dt_pagamento', '<=', $ate);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json(
            $query->orderBy('dt_pagamento', 'desc')->paginate(50)
        );
    }

    public function show(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $mov = MovBancario::where('empresa', $empresaId)->findOrFail($id);

        return response()->json($mov);
    }

    public function conciliar(Request $request, int $id)
    {
        $empresaId = $this->empresaId($request);

        $mov = MovBancario::where('empresa', $empresaId)->findOrFail($id);
        $mov->status = 'C'; // Conciliado (confirmar código no sistema atual)
        $mov->save();

        return response()->json($mov);
    }
}
