<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model de Preferências do sistema.
 * Sempre uma única linha (id = 1).
 */
class Preference extends Model
{
    protected $table = 'system_preferences';

    // Sem created_at — só updated_at
    const CREATED_AT = null;
    public $timestamps = false; // gerenciamos manualmente

    protected $fillable = [
        // E-mail
        'mail_from', 'mail_host', 'mail_port', 'mail_username',
        'mail_password', 'mail_encryption', 'mail_support',
        // Navegação
        'nav_single_tab', 'nav_show_version', 'nav_timeout',
        // Segurança
        'sec_strong_password', 'sec_recaptcha', 'sec_recaptcha_site',
        'sec_recaptcha_secret', 'sec_2fa_email', 'sec_2fa_google',
        'sec_2fa_subject', 'sec_2fa_body', 'sec_terms',
        // Manutenção
        'mnt_enabled', 'mnt_message', 'mnt_allowed_users',
        // Firebase
        'firebase_api_key', 'firebase_auth_domain', 'firebase_database_url',
        'firebase_project_id', 'firebase_storage_bucket',
        'firebase_sender_id', 'firebase_app_id',
        // Chat
        'chat_enabled', 'chat_blocked_profiles', 'chat_blocked_users',
    ];

    protected $casts = [
        'nav_single_tab'       => 'boolean',
        'nav_show_version'     => 'boolean',
        'sec_strong_password'  => 'boolean',
        'sec_recaptcha'        => 'boolean',
        'sec_2fa_email'        => 'boolean',
        'sec_2fa_google'       => 'boolean',
        'mnt_enabled'          => 'boolean',
        'chat_enabled'         => 'boolean',
        'mnt_allowed_users'    => 'array',
        'chat_blocked_profiles'=> 'array',
        'chat_blocked_users'   => 'array',
    ];

    /** Retorna as preferências (sempre id=1) */
    public static function get(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
