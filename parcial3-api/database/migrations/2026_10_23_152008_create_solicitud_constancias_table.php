<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Mail\ConstanciaLista;
use Illuminate\Support\Facades\Mail;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_de_constancias', function (Blueprint $table) {
            $table->id('id_constancias');
            $table->string('nombre_empleado');
            $table->string('correo');
            $table->string('movil');
            $table->string('tipo_de_constancia');
            $table->text('descripcion');
            $table->integer('cantidad');
            $table->date('fecha_de_solicitud');
            $table->time('hora_de_solicitud');
            $table->boolean('estado_solicitud')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_de_constancias');
    }
};

