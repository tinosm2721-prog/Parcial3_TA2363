<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'rol', 'activo', 'debe_cambiar_password'];

   protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_secret' => 'encrypted',
        'two_factor_confirmed' => 'boolean',
        'activo' => 'boolean',
        'debe_cambiar_password' => 'boolean',
    ];
}
}
