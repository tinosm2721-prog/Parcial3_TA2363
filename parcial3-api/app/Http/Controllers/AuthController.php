<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        if (!$user->activo) {
            return response()->json(['message' => 'Tu cuenta está inhabilitada. Contacta al administrador.'], 403);
        }

        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        $mfaToken = Str::random(40);
        Cache::put('mfa:' . $mfaToken, $user->id, now()->addMinutes(5));

        $respuesta = [
            'mfa_token' => $mfaToken,
            'needs_setup' => !$user->two_factor_confirmed,
        ];

        if (!$user->two_factor_confirmed) {
            $respuesta['otpauth_url'] = $google2fa->getQRCodeUrl(
                'Constancias ULS',
                $user->email,
                $user->two_factor_secret
            );
        }

        return response()->json($respuesta);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'mfa_token' => 'required|string',
            'code' => 'required|digits:6',
        ]);

        $userId = Cache::get('mfa:' . $request->mfa_token);

        if (!$userId) {
            return response()->json(['message' => 'La sesión expiró, inicia de nuevo'], 401);
        }

        $user = User::find($userId);
        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($user->two_factor_secret, $request->code, 2)) {
            return response()->json(['message' => 'Código incorrecto'], 422);
        }

        if (!$user->two_factor_confirmed) {
            $user->two_factor_confirmed = true;
            $user->save();
        }

        Cache::forget('mfa:' . $request->mfa_token);

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'debe_cambiar_password' => (bool) $user->debe_cambiar_password,
            ],
        ]);
    }

    // Cambio de contraseña (obligatorio si la cuenta tiene contraseña temporal)
    public function cambiarPassword(Request $request)
    {
        $datos = $request->validate([
            'password_actual'            => 'required|string',
            'password_nueva'             => 'required|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if (!Hash::check($datos['password_actual'], $usuario->password)) {
            return response()->json(['message' => 'La contraseña actual no es correcta'], 422);
        }

        $usuario->update([
            'password' => $datos['password_nueva'],
            'debe_cambiar_password' => false,
        ]);

        return response()->json(['message' => 'Contraseña actualizada']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}