<?php

namespace App\Http\Controllers\Api;

use App\Models\Empresa;
use App\Models\SystemUser;
use App\Models\SystemUnit;
use App\Models\SystemUserUnit;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    /**
     * Login com usuário/senha reais do banco (system_users).
     * Suporta hash bcrypt (padrão Laravel) e hash legado MD5
     * (alguns usuários antigos do sistema Mad Builder ainda usam MD5).
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        $user = SystemUser::where('login', $login)->first();

        if (!$user) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $hash = $user->password;
        $isValid = false;

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$')) {
            // bcrypt
            $isValid = Hash::check($password, $hash);
        } elseif (preg_match('/^[a-f0-9]{32}$/i', $hash)) {
            // MD5 legado
            $isValid = md5($password) === $hash;
        } else {
            // fallback: comparação direta (não deveria ocorrer)
            $isValid = $password === $hash;
        }

        if (!$isValid) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if (($user->active ?? 'Y') !== 'Y') {
            return response()->json(['message' => 'Usuário inativo'], 403);
        }

        $units = $this->unitsDoUsuario($user);

        $token = TokenService::issue([
            'user_id' => $user->id,
            'login' => $user->login,
            'name' => $user->name,
            'empresa_id' => null,
        ]);

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'login' => $user->login, 'name' => $user->name, 'email' => $user->email],
            'units' => $units,
            'menu' => [],
        ]);
    }

    /**
     * Unidades (system_unit) liberadas ao usuário. O id da unidade
     * corresponde ao id da tabela empresa (1=TRANSPORTE/SGALVAO, 2=ARMAZÉM, ...),
     * que é o valor gravado na coluna "empresa" das tabelas financeiras.
     */
    private function unitsDoUsuario(SystemUser $user): array
    {
        $unitIds = SystemUserUnit::where('system_user_id', $user->id)->pluck('system_unit_id')->all();
        if ($user->system_unit_id) {
            $unitIds[] = $user->system_unit_id;
        }
        $unitIds = array_unique(array_map('intval', $unitIds));

        $empresas = Empresa::whereIn('id', $unitIds)->get()->keyBy('id');

        return SystemUnit::whereIn('id', $unitIds)->orderBy('id')->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'empresa_id' => $u->id,
                'empresa_nome' => $empresas[$u->id]->nome ?? null,
                'empresa_sigla' => $empresas[$u->id]->sigla ?? null,
            ])->values()->all();
    }

    /**
     * Troca/seleção de empresa: devolve novo token já com empresa_id.
     */
    public function switchEmpresa(Request $request)
    {
        $request->validate(['unit_id' => 'required|integer']);

        $authUser = $this->authUser($request);
        $user = SystemUser::find($authUser['user_id'] ?? 0);
        abort_if(!$user, 401, 'Não autenticado');

        $unitId = (int) $request->input('unit_id');
        $unit = collect($this->unitsDoUsuario($user))->firstWhere('id', $unitId);

        if (!$unit) {
            return response()->json(['message' => 'Sem acesso a essa empresa'], 403);
        }

        $token = TokenService::issue([
            'user_id' => $user->id,
            'login' => $user->login,
            'name' => $user->name,
            'empresa_id' => $unit['empresa_id'],
        ]);

        return response()->json([
            'token' => $token,
            'active_unit' => $unit,
            'menu' => [],
        ]);
    }

    public function me(Request $request)
    {
        $authUser = $this->authUser($request);
        $userId = $authUser['user_id'] ?? null;
        abort_if(!$userId, 401, 'Não autenticado');

        $user = SystemUser::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $empresa = null;
        if (!empty($authUser['empresa_id'])) {
            $empresa = Empresa::find($authUser['empresa_id']);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'login' => $user->login,
                'name' => $user->name,
            ],
            'empresa' => $empresa,
        ]);
    }

    public function logout(Request $request)
    {
        // Token é stateless (sem sessão no servidor); o front apenas descarta o token.
        return response()->json(['message' => 'ok']);
    }
}
