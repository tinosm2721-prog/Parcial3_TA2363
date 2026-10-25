<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Dueño de la constancia: el usuario que la registró
    public function up(): void
    {
        Schema::table('solicitud_de_constancias', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id_constancias')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitud_de_constancias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
