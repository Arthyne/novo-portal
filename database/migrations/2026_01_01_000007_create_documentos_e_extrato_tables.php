<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome_arquivo', 250);
            $table->string('tipo', 50)->nullable();
            $table->integer('tamanho')->nullable();
            $table->string('caminho', 500)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->timestamp('data_upload')->useCurrent();
        });

        Schema::create('extrato_movimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portador_id')->constrained('portadores');
            $table->date('data_mov');
            $table->string('descricao', 250)->nullable();
            $table->decimal('valor', 14, 2);
            $table->string('tipo', 10)->nullable();
            $table->boolean('conciliado')->default(0);
            $table->timestamp('criado_em')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extrato_movimentos');
        Schema::dropIfExists('documentos');
    }
};
