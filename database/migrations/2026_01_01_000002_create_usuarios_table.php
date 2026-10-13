<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('login', 80)->unique();
            $table->char('senha', 64); // sha256 hex
            $table->string('email', 150)->nullable();
            $table->string('perfil', 30)->default('user');
            $table->boolean('ativo')->default(1);
            $table->timestamp('criado_em')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
