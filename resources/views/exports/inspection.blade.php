<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Inspección</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-top: 20px; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Inspección</h1>
        <p>ID: {{ $inspection->id }}</p>
    </div>

    <div class="section">Información General</div>
    <table>
        <tr>
            <th>Fecha de Envío</th>
            <td>{{ $inspection->submit_date ? $inspection->submit_date->format('d/m/Y') : 'N/A' }}</td>
            <th>Estado</th>
            <td>
                @switch($inspection->status)
                    @case(\App\Models\Inspection::PENDING_STATUS)
                        Pendiente
                        @break
                    @case(\App\Models\Inspection::ACTIVE_STATUS)
                        Activo
                        @break
                    @case(\App\Models\Inspection::ON_HOLD_STATUS)
                        En Espera
                        @break
                    @case(\App\Models\Inspection::UNDER_REVIEW_STATUS)
                        En Revisión
                        @break
                    @case(\App\Models\Inspection::COMPLETED_STATUS)
                        Completado
                        @break
                    @default
                        Desconocido
                @endswitch
            </td>
        </tr>
        <tr>
            <th>Fecha de Inicio</th>
            <td>{{ $inspection->start_date ? $inspection->start_date->format('d/m/Y') : 'N/A' }}</td>
            <th>Fecha de Finalización</th>
            <td>{{ $inspection->complete_date ? $inspection->complete_date->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Descripción</th>
            <td colspan="3">{{ $inspection->description }}</td>
        </tr>
    </table>

    <div class="section">Información del Producto</div>
    <table>
        <tr>
            <th>Planta</th>
            <td>{{ $inspection->plant->name ?? 'N/A' }}</td>
            <th>Producto</th>
            <td>{{ $inspection->product->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Cliente</th>
            <td>{{ $inspection->product->client->name ?? 'N/A' }}</td>
            <th>Inventario</th>
            <td>{{ $inspection->inventory }}</td>
        </tr>
    </table>

    <div class="section">Resultados</div>
    <table>
        <tr>
            <th>Total Aprobados</th>
            <td>{{ $inspection->total_approved }}</td>
            <th>Total Rechazados</th>
            <td>{{ $inspection->total_rejected }}</td>
        </tr>
    </table>

    @if($inspection->lots->count() > 0)
    <div class="section">Lotes</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Número de Lote</th>
                <th>Total Unidades</th>
                <th>Total Rechazos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspection->lots as $lot)
            <tr>
                <td>{{ $lot->id }}</td>
                <td>{{ $lot->lot_number }}</td>
                <td>{{ $lot->total_units }}</td>
                <td>{{ $lot->total_rejects }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($inspection->defects->count() > 0)
    <div class="section">Defectos</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspection->defects as $defect)
            <tr>
                <td>{{ $defect->id }}</td>
                <td>{{ $defect->name }}</td>
                <td>{{ $defect->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($inspection->reworks->count() > 0)
    <div class="section">Retrabajo</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inspection->reworks as $rework)
            <tr>
                <td>{{ $rework->id }}</td>
                <td>{{ $rework->name }}</td>
                <td>{{ $rework->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>