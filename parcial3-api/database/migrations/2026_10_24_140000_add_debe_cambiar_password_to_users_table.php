<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cuentas creadas por el administrador tienen contraseña temporal: deben cambiarla al entrar
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('debe_cambiar_password')->default(false)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('debe_cambiar_password');
        });
    }
};
