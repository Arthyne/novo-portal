<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_empresa', function (Blueprint $table) {
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->primary(['usuario_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_empresa');
    }
};
