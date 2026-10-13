<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Geração/validação de token de autenticação sem depender de pacotes externos
 * (usa a criptografia nativa do Laravel, baseada na APP_KEY já configurada).
 */
class TokenService
{
    public static function issue(array $payload, int $ttlHours = 8): string
    {
        $payload['exp'] = now()->addHours($ttlHours)->timestamp;
        return Crypt::encryptString(json_encode($payload));
    }

    public static function verify(string $token): ?array
    {
        try {
            $data = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException $e) {
            return null;
        }

        if (!is_array($data) || !isset($data['exp']) || $data['exp'] < time()) {
            return null;
        }

        return $data;
    }
}
