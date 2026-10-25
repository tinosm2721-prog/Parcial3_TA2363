<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudConstancia extends Model
{
    protected $table = 'solicitud_de_constancias';
    protected $primaryKey = 'id_constancias';

    protected $fillable = [
        'user_id', 'nombre_empleado', 'correo', 'movil', 'tipo_de_constancia',
        'descripcion', 'cantidad', 'fecha_de_solicitud',
        'hora_de_solicitud', 'estado_solicitud',
    ];

    protected $casts = ['estado_solicitud' => 'boolean'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($c) {
            $c->fecha_de_solicitud = now()->toDateString();
            $c->hora_de_solicitud  = now()->toTimeString();
            $c->estado_solicitud   = true;
        });
    }
}