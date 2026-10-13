<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;

class AuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return response()->json(['message' => 'Token ausente'], 401);
        }

        $payload = TokenService::verify($m[1]);
        if (!$payload) {
            return response()->json(['message' => 'Token inválido ou expirado'], 401);
        }

        // Disponibiliza os dados do usuário autenticado para os controllers
        $request->attributes->set('auth_user', $payload);

        return $next($request);
    }
}
