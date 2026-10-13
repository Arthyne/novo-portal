<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('empresas')->count() > 0) {
            return;
        }

        DB::table('empresas')->insert([
            ['nome' => 'Transporte', 'cnpj' => '00.000.000/0001-00', 'ativo' => 1],
            ['nome' => 'Armazém', 'cnpj' => '00.000.000/0002-00', 'ativo' => 1],
        ]);

        DB::table('usuarios')->insert([
            'nome' => 'Administrador',
            'login' => 'admin',
            'senha' => hash('sha256', 'admin123'),
            'email' => 'admin@sgalvao.com',
            'perfil' => 'admin',
            'ativo' => 1,
        ]);

        $usuarioId = DB::table('usuarios')->where('login', 'admin')->value('id');
        $empresas = DB::table('empresas')->pluck('id');

        foreach ($empresas as $empresaId) {
            DB::table('usuario_empresa')->insert([
                'usuario_id' => $usuarioId,
                'empresa_id' => $empresaId,
            ]);
        }
    }

    public function down(): void
    {
        // Seed não é revertido
    }
};
