<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('descricao', 250);
            $table->foreignId('pessoa_id')->nullable()->constrained('pessoas');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->foreignId('centro_custo_id')->nullable()->constrained('centros_custo');
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamento');
            $table->foreignId('portador_id')->nullable()->constrained('portadores');
            $table->decimal('valor_total', 14, 2);
            $table->integer('num_parcelas')->default(1);
            $table->string('modo_parcelamento', 20)->default('dividido');
            $table->date('data_emissao')->nullable();
            $table->string('status', 20)->default('aberto');
            $table->timestamp('criado_em')->useCurrent();
        });

        Schema::create('contas_pagar_parcelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas_pagar');
            $table->integer('numero');
            $table->decimal('valor', 14, 2);
            $table->date('vencimento');
            $table->string('status', 20)->default('aberto');
            $table->dateTime('data_pagamento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_pagar_parcelas');
        Schema::dropIfExists('contas_pagar');
    }
};
