<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Usuario habilitado (true) o inhabilitado (false); los inhabilitados no pueden iniciar sesión
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
