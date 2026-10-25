<?php

namespace App\Http\Controllers;

use App\Models\SolicitudConstancia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    // El administrador ve todas las constancias; el registrador solo las que él registró
    private function visibles(Request $request)
    {
        $query = SolicitudConstancia::query();

        if ($request->user()->rol !== 'administrador') {
            $query->where('user_id', $request->user()->id);
        }

        return $query;
    }

    // Resumen: cantidad de constancias registradas por mes
    private function porMes(Request $request)
    {
        return $this->visibles($request)->get()
            ->groupBy(fn ($c) => Carbon::parse($c->fecha_de_solicitud)->format('Y-m'))
            ->map(fn ($grupo, $mes) => [
                'mes'       => $mes,
                'cantidad'  => $grupo->count(),
                'activas'   => $grupo->where('estado_solicitud', true)->count(),
                'inactivas' => $grupo->where('estado_solicitud', false)->count(),
            ])
            ->sortKeys()
            ->values();
    }

    public function pdf(Request $request)
    {
        $pdf = Pdf::loadView('reportes.constancias', [
            'meses'       => $this->porMes($request),
            'constancias' => $this->visibles($request)->with('usuario')->orderBy('id_constancias')->get(),
        ]);

        return $pdf->stream('informe_constancias.pdf');
    }

    // CSV con encabezado de datos del informe y luego el detalle de constancias
    public function csv(Request $request)
    {
        $constancias = $this->visibles($request)->with('usuario')
            ->orderBy('fecha_de_solicitud')->orderBy('id_constancias')->get();

        $rango = $constancias->isEmpty()
            ? '—'
            : Carbon::parse($constancias->first()->fecha_de_solicitud)->format('d/m/Y')
                . ' - '
                . Carbon::parse($constancias->last()->fecha_de_solicitud)->format('d/m/Y');

        $encabezado = [
            ['institucion', 'Universidad Luterana Salvadoreña'],
            ['establecimiento', 'Sistema de Gestión de Constancias ULS'],
            ['rango de fecha', $rango],
            ['usuario genera', $request->user()->name],
            ['fecha hora genera', now()->format('d/m/Y H:i')],
            ['recursos reportados', $constancias->pluck('tipo_de_constancia')->unique()->count()],
            ['opiniones registradas', $constancias->count()],
            [],
            ['fecha', 'empleado', 'tipo', 'estado', 'registrado por'],
        ];

        $detalle = $constancias->map(fn ($c) => [
            Carbon::parse($c->fecha_de_solicitud)->format('d/m/Y'),
            $c->nombre_empleado,
            $c->tipo_de_constancia,
            $c->estado_solicitud ? 'Activa' : 'Inactiva',
            $c->usuario?->name ?? '—',
        ])->all();

        return response()->streamDownload(function () use ($encabezado, $detalle) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // para que Excel lea bien las tildes
            foreach (array_merge($encabezado, $detalle) as $fila) {
                fputcsv($out, $fila, ',', '"', '');
            }
            fclose($out);
        }, 'constancias.csv', ['Content-Type' => 'text/csv']);
    }

    // Datos del gráfico: ?mes=10&anio=2026
    public function grafico(Request $request)
    {
        $request->validate([
            'mes'  => 'required|integer|between:1,12',
            'anio' => 'required|integer',
        ]);

        $base = SolicitudConstancia::whereYear('fecha_de_solicitud', $request->anio)
            ->whereMonth('fecha_de_solicitud', $request->mes);

        return response()->json([
            'activas'   => (clone $base)->where('estado_solicitud', true)->count(),
            'inactivas' => (clone $base)->where('estado_solicitud', false)->count(),
        ]);
    }
}
