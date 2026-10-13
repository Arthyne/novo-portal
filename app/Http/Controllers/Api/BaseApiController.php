<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    protected function authUser(Request $request): array
    {
        return $request->attributes->get('auth_user', []);
    }

    protected function empresaId(Request $request): int
    {
        $empresaId = $this->authUser($request)['empresa_id'] ?? null;
        abort_if(!$empresaId, 400, 'Nenhuma empresa selecionada');
        return (int) $empresaId;
    }
}
