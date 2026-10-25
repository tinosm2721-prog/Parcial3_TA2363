<h2>Hola {{ $constancia->nombre_empleado }}</h2>
<p>Tu solicitud de constancia ya está lista.</p>
<ul>
    <li><strong>Tipo:</strong> {{ $constancia->tipo_de_constancia }}</li>
    <li><strong>Cantidad:</strong> {{ $constancia->cantidad }}</li>
    <li><strong>Fecha de solicitud:</strong> {{ $constancia->fecha_de_solicitud }}</li>
</ul>
<p>Puedes pasar a recogerla.</p>