<?php

namespace App\Http\Controllers;

use App\Models\SolicitudConstancia;
use Illuminate\Http\Request;
use App\Mail\ConstanciaLista;
use Illuminate\Support\Facades\Mail;

class ConstanciaController extends Controller
{
    // Listado paginado de 5. Filtro opcional: ?estado=1 (activas) o ?estado=0 (inactivas)
    public function index(Request $request)
    {
        $query = SolicitudConstancia::orderByDesc('id_constancias');

        if ($request->filled('estado')) {
            $query->where('estado_solicitud', filter_var($request->estado, FILTER_VALIDATE_BOOLEAN));
        }

        return $query->paginate(5);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre_empleado'    => 'required|string|max:100',
            'correo'             => 'required|email|max:100',
            'movil'              => 'required|digits:8',
            'tipo_de_constancia' => 'required|string|max:100',
            'descripcion'        => 'required|string|max:500',
            'cantidad'           => 'required|integer|min:1',
        ]);

        $constancia = SolicitudConstancia::create($datos + ['user_id' => $request->user()->id]);

        return response()->json($constancia, 201);
    }

    // Constancias registradas por el usuario que hace la petición
    public function misConstancias(Request $request)
    {
        return SolicitudConstancia::where('user_id', $request->user()->id)
            ->orderByDesc('id_constancias')
            ->get();
    }

    // Activa/inactiva. El registrador solo puede cambiar las suyas
    public function cambiarEstado(Request $request, SolicitudConstancia $constancia)
    {
        $datos = $request->validate(['estado_solicitud' => 'required|boolean']);

        $usuario = $request->user();
        if ($usuario->rol !== 'administrador' && $constancia->user_id !== $usuario->id) {
            return response()->json(['message' => 'No puedes modificar esta constancia'], 403);
        }

        $constancia->update($datos);

        return response()->json($constancia);
    }

    public function show(SolicitudConstancia $constancia)
    {
        return $constancia;
    }

    public function update(Request $request, SolicitudConstancia $constancia)
    {
        $datos = $request->validate([
            'nombre_empleado'    => 'required|string|max:100',
            'correo'             => 'required|email|max:100',
            'movil'              => 'required|digits:8',
            'tipo_de_constancia' => 'required|string|max:100',
            'descripcion'        => 'required|string|max:500',
            'cantidad'           => 'required|integer|min:1',
            'estado_solicitud'   => 'sometimes|boolean',
        ]);

        $constancia->update($datos);

        return response()->json($constancia);
    }

        public function destroy(SolicitudConstancia $constancia)
    {
        $constancia->delete();

        return response()->json(['message' => 'Constancia eliminada']);
    }

    public function enviarCorreo(SolicitudConstancia $constancia)
    {
        Mail::to($constancia->correo)->send(new ConstanciaLista($constancia));

        return response()->json(['message' => 'Correo enviado a ' . $constancia->correo]);
    }
}