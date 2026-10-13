<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome', 150);
            $table->string('tipo', 30)->nullable();
            $table->string('banco', 100)->nullable();
            $table->string('agencia', 20)->nullable();
            $table->string('conta', 30)->nullable();
            $table->decimal('saldo_inicial', 14, 2)->default(0);
            $table->boolean('ativo')->default(1);
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome', 150);
            $table->string('tipo', 20)->nullable();
            $table->foreignId('categoria_pai_id')->nullable()->constrained('categorias');
            $table->boolean('ativo')->default(1);
        });

        Schema::create('centros_custo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome', 150);
            $table->string('codigo', 30)->nullable();
            $table->boolean('ativo')->default(1);
        });

        Schema::create('formas_pagamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome', 100);
            $table->string('tipo', 30)->nullable();
            $table->boolean('ativo')->default(1);
        });

        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome', 200);
            $table->string('tipo', 20)->nullable();
            $table->string('documento', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->boolean('ativo')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas');
        Schema::dropIfExists('formas_pagamento');
        Schema::dropIfExists('centros_custo');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('portadores');
    }
};
