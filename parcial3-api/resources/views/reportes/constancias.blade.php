<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background: #e0e0e0; }
    </style>
</head>
<body>
    <h2>Informe de Constancias</h2>

    <h3>Constancias registradas por mes</h3>
    <table>
        <tr><th>Mes</th><th>Cantidad</th><th>Activas</th><th>Inactivas</th></tr>
        @foreach ($meses as $m)
            <tr>
                <td>{{ $m['mes'] }}</td>
                <td>{{ $m['cantidad'] }}</td>
                <td>{{ $m['activas'] }}</td>
                <td>{{ $m['inactivas'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>Detalle de constancias</h3>
    <table>
        <tr>
            <th>ID</th><th>Empleado</th><th>Correo</th><th>Tipo</th>
            <th>Cant.</th><th>Fecha</th><th>Estado</th><th>Registrado por</th>
        </tr>
        @foreach ($constancias as $c)
            <tr>
                <td>{{ $c->id_constancias }}</td>
                <td>{{ $c->nombre_empleado }}</td>
                <td>{{ $c->correo }}</td>
                <td>{{ $c->tipo_de_constancia }}</td>
                <td>{{ $c->cantidad }}</td>
                <td>{{ $c->fecha_de_solicitud }}</td>
                <td>{{ $c->estado_solicitud ? 'Activa' : 'Inactiva' }}</td>
                <td>{{ $c->usuario?->name ?? '—' }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>