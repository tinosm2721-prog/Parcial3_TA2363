<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Lista de usuarios creados, para la pantalla de registro del administrador
    public function index()
    {
        return User::select('id', 'name', 'email', 'rol', 'activo', 'created_at')->orderByDesc('id')->get();
    }

    public function show(User $usuario)
    {
        return $usuario->only('id', 'name', 'email', 'rol', 'activo', 'created_at');
    }

    // Edita nombre, correo y rol. La contraseña es opcional: solo se cambia si se envía
    public function update(Request $request, User $usuario)
    {
        $datos = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => 'nullable|string|min:8',
            'rol'      => ['required', Rule::in(['administrador', 'registrador'])],
        ]);

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        if ($usuario->id === $request->user()->id && $datos['rol'] !== 'administrador') {
            return response()->json(['message' => 'No puedes quitarte el rol de administrador'], 422);
        }

        $usuario->update($datos);

        return $usuario->only('id', 'name', 'email', 'rol', 'activo', 'created_at');
    }

    // Habilita o inhabilita. Un usuario inhabilitado no puede iniciar sesión
    public function estado(Request $request, User $usuario)
    {
        $datos = $request->validate(['activo' => 'required|boolean']);

        if ($usuario->id === $request->user()->id && !$datos['activo']) {
            return response()->json(['message' => 'No puedes inhabilitar tu propia cuenta'], 422);
        }

        $usuario->update($datos);

        return $usuario->only('id', 'name', 'email', 'rol', 'activo', 'created_at');
    }

    // Solo el administrador crea cuentas; el rol lo elige él (administrador o registrador)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
            'rol'      => ['required', Rule::in(['administrador', 'registrador'])],
        ]);

        // La contraseña que pone el administrador es temporal: el usuario la cambia al entrar
        $usuario = User::create($datos + ['debe_cambiar_password' => true]);

        return response()->json($usuario, 201);
    }

    // Registro público desde el login: siempre crea registradores, nunca administradores
    public function registro(Request $request)
    {
        $datos = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
        ]);

        $usuario = User::create($datos + ['rol' => 'registrador']);

        return response()->json($usuario, 201);
    }
}
