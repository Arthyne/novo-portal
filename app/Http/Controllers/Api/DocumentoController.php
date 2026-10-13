<?php

namespace App\Http\Controllers\Api;

use App\Models\Documento;
use Illuminate\Http\Request;

class DocumentoController extends BaseApiController
{
    public function index(Request $request)
    {
        $docs = Documento::where('empresa_id', $this->empresaId($request))
            ->orderByDesc('data_upload')
            ->get();

        return response()->json($docs);
    }

    public function destroy(Request $request, int $id)
    {
        $doc = Documento::where('id', $id)->where('empresa_id', $this->empresaId($request))->first();
        abort_if(!$doc, 404, 'Documento não encontrado');

        $doc->delete();
        return response()->json(['ok' => true]);
    }
}
