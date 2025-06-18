<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mission Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .header {
            background-color: #030503;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            padding: 4px;
            vertical-align: middle;
        }


        .company-info {
            text-align: right;
            font-size: 9px;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 10px;
        }

        .main-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .section-header {
            background-color: #030503;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }

        .date-location {
            text-align: center;
            font-weight: bold;
            margin: 5px 0;
        }

        .properties-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .properties-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 9px;
        }

        .properties-table .prop-label {
            background-color: #e6f3ff;
            font-weight: bold;
            width: 25%;
        }

        .charts-section {
            margin: 10px 0;
        }

        .charts-header {
            background-color: #030503;
            color: white;
            text-align: center;
            padding: 5px;
            font-weight: bold;
        }

        .charts-container {
            display: block;
            background-color: #f8f8f8;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .chart-section {
            float: left;
            width: 30%;
            text-align: center;
        }

        .chart-section.right {
            float: right;
            width: 65%;
        }

        .chart-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .chart-img {
            max-width: 100%;
            height: auto;
        }

        .stats-table {
            border: 1px solid #408833;
            background-color: #40883320;
            border-collapse: collapse;
            margin-top: 10px;
            width: 100%;
        }

        .stats-table td {
            border: 1px solid #408833;
            padding: 3px 8px;
            font-size: 9px;
        }

        .stats-label {
            background-color: #40883320;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            margin-bottom: 10px;
            padding: 3px 5px;
            text-align: left;
        }

        .data-table th {
            background-color: #ebebeb;
            font-weight: bold;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .defect-header {
            background-color: #030503;
            color: #FFF;
            text-align: center;
            padding: 5px;
            font-weight: bold;
        }

        .additional-costs-header {
            background-color: #030503;
            color: #FFF;
            text-align: center;
            padding: 5px;
            font-weight: bold;
        }

        .defect-images {
            text-align: center;
            padding: 10px;
        }

        .defect-images img {
            width: 80px;
            height: 100px;
            margin: 0 5px;
            border: 1px solid #ccc;
        }

        .signature-section {
            margin-top: 30px;
            text-align: left;
        }

        .signature-section p {
            margin: 2px 0;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        .page-break {
            page-break-before: always;
        }

        .portal-link {
            font-size: 8px;
            text-align: right;
            color: #666;
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 0;">
                    <img src="{{ public_path('logo.png') }}" alt="Payer Logo" style="width: auto; height: 32px;">
                </td>
                <td style="width: 0; text-align: left; white-space: nowrap;">
                    {{ $reportData['mission_info']['report_type'] }}
                </td>
                <td style="text-align: center;">
                    {{ $reportData['mission_info']['mission'] }}
                </td>
                <td style="width: 0; text-align: right; white-space: nowrap;">
                    {{ $reportData['mission_info']['reference'] }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Main Info Table -->
    <table class="main-table">
        <tr>
            <td style="width: 45%;">
                <strong>Para:</strong><br>
                {{ $reportData['mission_info']['recipient_name'] }}<br>
            </td>
            <td style="width: 55%;">
                <strong>Lista de distribución:</strong><br>
                <span style="white-space: pre-line;">
                    {{ $reportData['mission_info']['distribution_list'] }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Date and Location -->
    <div class="date-location">
        {{ $reportData['mission_info']['location'] }}, {{ $reportData['mission_info']['date'] }}, {{ $reportData['mission_info']['time'] }}
    </div>

    <!-- Mission Properties -->
    <div class="section-header">Propiedades de la misión</div>
    <table class="properties-table">
        <tr>
            <td class="prop-label">Tipo de servicio:</td>
            <td style="width: 40%;">{{ $reportData['mission_properties']['service_type'] }}</td>
            <td class="prop-label">Cantidad aceptada:</td>
            <td>{{ $reportData['mission_properties']['accepted_quantity'] }}</td>
        </tr>
        <tr>
            <td class="prop-label">Fecha de inicio:</td>
            <td>{{ $reportData['mission_properties']['start_date'] }}</td>
            <td class="prop-label">Cantidad rechazada:</td>
            <td>{{ $reportData['mission_properties']['rejected_quantity'] }}</td>
        </tr>
        <tr>
            <td class="prop-label">Estado de la misión:</td>
            <td>{{ $reportData['mission_properties']['status'] }}</td>
            <td class="prop-label">QN:</td>
            <td>{{ $reportData['mission_properties']['QN'] }}</td>
        </tr>
        <tr>
            <td class="prop-label">Planta:</td>
            <td>{{ $reportData['mission_properties']['plant'] }}</td>
            <td class="prop-label">Supervisor a cargo:</td>
            <td style="white-space: nowrap;">{{ $reportData['mission_properties']['supervisor'] }}</td>
        </tr>
    </table>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="charts-header">Síntesis de gráficos de {{$reportData['date_from']}} a {{$reportData['date_to']}}</div>
        <div class="charts-container clearfix">
            <div class="chart-section">
                <div class="chart-title">Piezas retrabajadas</div>
                <img src="{{ $chartUrls['donut'] }}" alt="Donut Chart" class="chart-img">
                <table class="stats-table">
                    <tr>
                        <td class="stats-label">Semana en curso (W{{$reportData['week']}})</td>
                        <td>{{ $reportData['statistics']['week_in_progress'] }}</td>
                    </tr>
                    <tr>
                        <td class="stats-label">Mes en curso ({{$reportData['month']}})</td>
                        <td>{{ $reportData['statistics']['month_in_progress'] }}</td>
                    </tr>
                    <tr>
                        <td class="stats-label">Total misión (desde {{$reportData['date_from']}})</td>
                        <td>{{ $reportData['statistics']['total_mission'] }}</td>
                    </tr>
                </table>
                <div style="font-style: italic; font-size: 8px; margin-top: 5px;">(Valores absolutos)</div>
            </div>

            <div class="chart-section right">
                <div class="chart-title">Pareto por referencias</div>
                <img src="{{ $chartUrls['pareto'] }}" alt="Pareto Chart" class="chart-img">
            </div>
        </div>
    </div>

    <!-- <div class="portal-link">
        Más detalles en el Portal Cliente TRIGO <span style="text-decoration: underline;">https://portal.trigo-group.com</span>
    </div> -->

    <!-- Parts Data Table -->
    <div style="margin-bottom: 10px">
        <div class="section-header">Información de las cantidades desde {{$reportData['date_from']}} a {{$reportData['date_to']}}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ $reportData['parts_headers']['index'] }}</th>
                    @foreach($reportData['parts_headers']['custom_attributes'] as $attribute)
                    <th>{{ $attribute }}</th>
                    @endforeach
                    <th>{{ $reportData['parts_headers']['accepted'] }}</th>
                    <th>{{ $reportData['parts_headers']['rejected'] }}</th>
                    <th>{{ $reportData['parts_headers']['reject_percentage'] }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                $total_inspected = 0;
                $total_rejected = 0;
                @endphp
                @foreach($reportData['parts_data'] as $part)
                <tr>
                    <td>{{ $part['index'] }}</td>
                    @foreach($part['custom_attributes'] as $attribute)
                    <td>{{ $attribute }}</td>
                    @endforeach
                    <td>{{ $part['accepted'] }}</td>
                    <td>{{ $part['rejected'] }}</td>
                    <td>{{ $part['reject_percentage'] }}</td>
                </tr>
                @php
                $total_inspected += $part['accepted'] + $part['rejected'];
                $total_rejected += (int)$part['rejected'];
                @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="{{ 1 + count($reportData['parts_headers']['custom_attributes']) }}" style="text-align: right;"><strong>Total</strong></td>
                    <td><strong>{{ $total_inspected - $total_rejected }}</strong></td>
                    <td><strong>{{ $total_rejected }}</strong></td>
                    <td>{{ $total_inspected ? number_format($total_rejected / $total_inspected, 2) : ' - ' }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- <div style="font-size: 8px; margin: 5px 0;">
        * Sin sorting para esta referencia
    </div> -->

    <div class="section-header">Monte rechazado del período desde {{$reportData['date_from']}} hasta {{$reportData['date_to']}}</div>

    <!-- Page Break for Images Section -->
    <div class="page-break"></div>

    <!-- Defect Images Section -->
    <div style="margin-bottom: 10px">
        <div class="defect-header">Detalle de las horas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 80px">Fecha</th>
                    <th>Defectos</th>
                    <th style="width: 0;white-space: nowrap">Cantidad de imágenes subidas</th>
                </tr>
            </thead>
            <tbody>
                @php $total_samples = 0; $photos_count = 0; @endphp
                @foreach($reportData['defect_images'] as $image)
                @php $photos_count = count($image['photos']); @endphp
                <tr>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $image['date'] }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $image['defect_name'] }}</td>
                    <td style="border: 1px solid #000; padding: 5px;">{{ $photos_count }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 10px;">
                        <div style="text-align: center">
                            @foreach($image['photos'] as $photo)
                            <div style="display:inline-block; vertical-align:top; position: relative;width: 120px; height: 160px">
                                <img src="{{ $photo }}" alt="Defect Image" style="position:absolute; top:0;bottom:0;left:0;right:0;width:100%;height:100%">
                            </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @php $total_samples += $photos_count; @endphp
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;"><strong>Total muestras</strong></td>
                    <td><strong>{{ $total_samples }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Additional Sections -->
    <div style="margin-bottom: 10px;">
        <div class="additional-costs-header">Costes adicionales</div>
        <table class="data-table">
            <tr>
                <th>Descripción defectos</td>
                <th style="width: 0">Cantidad</td>
                <th>Comentarios</td>
                <th>Otro</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 20px;">&nbsp;</td>
                <td style="border: 1px solid #000; padding: 20px;">&nbsp;</td>
                <td style="border: 1px solid #000; padding: 20px;">&nbsp;</td>
                <td style="border: 1px solid #000; padding: 20px;">&nbsp;</td>
            </tr>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <p>Quedamos a su disposición para cualquier información adicional.</p>
        <p>Atentamente,</p>
        <br>
        <p><strong>{{ $reportData['signer']['name'] }}</strong></p>
        <p>{{ $reportData['signer']['title'] }}</p>
        <p>Email: {{ $reportData['signer']['email'] }}</p>
    </div>
</body>

</html>