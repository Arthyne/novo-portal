<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Recria a tabela documentos com estrutura completa de NF-e
        Schema::dropIfExists('documentos');
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo', 10);                        // NF, FAT, REC, CTR, OUT
            $table->string('num_documento', 60)->nullable();
            $table->string('serie', 10)->nullable();
            $table->unsignedBigInteger('pessoa_id')->nullable();
            $table->string('chave_acesso', 44)->nullable();
            $table->date('dt_emissao')->nullable();
            $table->date('dt_competencia')->nullable();
            $table->decimal('valor_total', 14, 2)->nullable();
            $table->string('descricao', 250)->nullable();
            $table->text('observacao')->nullable();
            $table->string('status', 20)->default('ATIVO');
            $table->string('arquivo_path', 500)->nullable();   // PDF
            $table->string('arquivo_nome', 250)->nullable();
            $table->string('xml_path', 500)->nullable();       // XML original
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('pessoa_id')->references('id')->on('tabcontato');
        });

        // Adiciona documento_id nas tabelas de contas
        Schema::table('new_contaspagar', function (Blueprint $table) {
            $table->unsignedBigInteger('documento_id')->nullable()->after('id');
            $table->foreign('documento_id')->references('id')->on('documentos')->nullOnDelete();
        });

        Schema::table('new_contasreceber', function (Blueprint $table) {
            $table->unsignedBigInteger('documento_id')->nullable()->after('id');
            $table->foreign('documento_id')->references('id')->on('documentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('new_contasreceber', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropColumn('documento_id');
        });
        Schema::table('new_contaspagar', function (Blueprint $table) {
            $table->dropForeign(['documento_id']);
            $table->dropColumn('documento_id');
        });
        Schema::dropIfExists('documentos');
    }
};
