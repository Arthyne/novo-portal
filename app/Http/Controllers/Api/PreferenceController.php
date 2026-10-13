<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Preference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class PreferenceController extends Controller
{
    /** GET /api/preferences — retorna as configurações atuais */
    public function index(): JsonResponse
    {
        $prefs = Preference::get();

        // Nunca devolver senhas/secrets em texto puro
        $data = $prefs->toArray();
        if ($data['mail_password']) {
            $data['mail_password'] = '••••••••'; // mascara no retorno
        }
        if ($data['sec_recaptcha_secret']) {
            $data['sec_recaptcha_secret'] = '••••••••';
        }
        if ($data['firebase_api_key']) {
            $data['firebase_api_key'] = '••••••••';
        }

        return response()->json(['data' => $data]);
    }

    /** PUT /api/preferences — salva todas as configurações */
    public function update(Request $request): JsonResponse
    {
        $prefs = Preference::get();

        $payload = $request->except(['id', 'updated_at']);

        // Criptografa campos sensíveis antes de salvar
        foreach (['mail_password', 'sec_recaptcha_secret', 'firebase_api_key'] as $field) {
            if (isset($payload[$field]) && $payload[$field] && $payload[$field] !== '••••••••') {
                $payload[$field] = Crypt::encryptString($payload[$field]);
            } else {
                // Mantém o valor atual se veio mascarado
                unset($payload[$field]);
            }
        }

        $prefs->fill($payload);
        $prefs->updated_at = now();
        $prefs->save();

        return response()->json(['message' => 'Preferências salvas com sucesso.']);
    }

    /** POST /api/preferences/test-email — envia e-mail de teste */
    public function testEmail(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
        ]);

        $prefs = Preference::get();

        // Configura SMTP dinamicamente para o teste
        config([
            'mail.mailers.smtp.host'       => $prefs->mail_host,
            'mail.mailers.smtp.port'       => $prefs->mail_port,
            'mail.mailers.smtp.username'   => $prefs->mail_username,
            'mail.mailers.smtp.password'   => $prefs->mail_password
                ? Crypt::decryptString($prefs->mail_password)
                : '',
            'mail.mailers.smtp.encryption' => $prefs->mail_encryption,
            'mail.from.address'            => $prefs->mail_from,
            'mail.from.name'               => 'SGalvão Connect',
        ]);

        try {
            Mail::raw(
                'Este é um e-mail de teste do SGalvão Connect. Se você recebeu esta mensagem, as configurações SMTP estão corretas.',
                function (Message $msg) use ($request, $prefs) {
                    $msg->to($request->to)
                        ->subject('Teste de e-mail — SGalvão Connect')
                        ->from($prefs->mail_from ?? 'sistema@portalgruposgalvao.com.br', 'SGalvão Connect');
                }
            );

            return response()->json(['message' => 'E-mail de teste enviado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar: ' . $e->getMessage()], 422);
        }
    }

    /** GET /api/preferences/maintenance-status — usado pelo middleware de manutenção */
    public function maintenanceStatus(): JsonResponse
    {
        $prefs = Preference::get();
        return response()->json([
            'enabled'       => $prefs->mnt_enabled,
            'message'       => $prefs->mnt_message,
            'allowed_users' => $prefs->mnt_allowed_users ?? [],
        ]);
    }
}
