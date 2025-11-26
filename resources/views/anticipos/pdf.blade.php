<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 9px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
        }
        table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-section {
            margin-top: 20px;
        }
        .summary-table {
            width: 50%;
            float: left;
        }
        .footer {
            margin-top: 30px;
            clear: both;
        }
        .footer-row {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LIQUIDACION DE GASTOS POR:</h1>
        <h2>{{ strtoupper($anticipo->tipo == 'anticipo' ? 'ANTICIPO' : 'REEMBOLSO') }}</h2>
    </div>

    <!-- Información del Solicitante -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">EMPRESA:</span>
            <span class="info-value">{{ $anticipo->area->nombre ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NOMBRE SOLICITANTE:</span>
            <span class="info-value">{{ $anticipo->usuario->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">CARGO:</span>
            <span class="info-value">{{ $anticipo->usuario->role ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">FECHA DE SOLICITUD:</span>
            <span class="info-value">{{ $anticipo->fecha->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">FECHA RENDICIÓN:</span>
            <span class="info-value">{{ $anticipo->comprobantes->max('fecha') ? \Carbon\Carbon::parse($anticipo->comprobantes->max('fecha'))->format('d/m/Y') : 'N/A' }}</span>
        </div>
        @if($anticipo->banco)
        <div class="info-row">
            <span class="info-label">BANCO:</span>
            <span class="info-value">{{ $anticipo->banco->descripcion }}</span>
        </div>
        @endif
    </div>

    <!-- Tabla de Asignación -->
    <div class="info-section">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">RICHA</th>
                    <th style="width: 15%;">TIPO DOCUMENTO</th>
                    <th style="width: 15%;">N° CUENTA</th>
                    <th style="width: 20%;">BANCO</th>
                    <th style="width: 25%;">CONCEPTO</th>
                    <th style="width: 15%;" class="text-right">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ strtoupper($anticipo->tipo) }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $anticipo->banco->descripcion ?? '-' }}</td>
                    <td>{{ $anticipo->descripcion ?? 'ANTICIPO/REEMBOLSO' }}</td>
                    <td class="text-right">{{ number_format($anticipo->importe, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tabla de Detalle -->
    <div class="info-section">
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">FECHA</th>
                    <th style="width: 15%;">TIPO DOCUMENTO</th>
                    <th style="width: 15%;">N° DOCUMENTO</th>
                    <th style="width: 20%;">DENOMINACIÓN / RAZÓN SOCIAL</th>
                    <th style="width: 20%;">DESCRIPCIÓN</th>
                    <th style="width: 10%;" class="text-right">HABER</th>
                    <th style="width: 10%;" class="text-right">SALDO</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $saldo = 0;
                    $totalGastos = 0;
                @endphp
                @foreach($anticipo->comprobantes->sortBy('fecha') as $comprobante)
                    @php
                        $tipoComprobante = \App\Models\TipoComprobante::where('codigo', $comprobante->tipo)->first();
                        $saldo += $comprobante->monto;
                        $totalGastos += $comprobante->monto;
                    @endphp
                    <tr>
                        <td>{{ $comprobante->fecha->format('d/m/Y') }}</td>
                        <td>{{ $tipoComprobante->descripcion ?? $comprobante->tipo }}</td>
                        <td>{{ $comprobante->id }}</td>
                        <td>{{ $comprobante->detalle ?? '-' }}</td>
                        <td>{{ $comprobante->detalle ?? 'GASTO VARIOS' }}</td>
                        <td class="text-right">{{ number_format($comprobante->monto, 2) }}</td>
                        <td class="text-right">({{ number_format($saldo, 2) }})</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="text-right"><strong>TOTALES</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalGastos, 2) }}</strong></td>
                    <td class="text-right"><strong>({{ number_format($saldo, 2) }})</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Resumen por Conceptos -->
    <div class="summary-section">
        <div class="info-row" style="margin-bottom: 10px;">
            <span class="info-label"><strong>(A) IMPORTE RECIBIDO:</strong></span>
            <span class="info-value">{{ number_format($anticipo->importe, 2) }}</span>
        </div>

        <table class="summary-table" style="width: 50%;">
            <thead>
                <tr>
                    <th style="width: 20%;">N° COMPROB.</th>
                    <th style="width: 50%;">CONCEPTOS</th>
                    <th style="width: 30%;" class="text-right">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $resumenConceptos = [];
                    foreach($anticipo->comprobantes as $comp) {
                        $tipoComp = \App\Models\TipoComprobante::where('codigo', $comp->tipo)->first();
                        $concepto = $tipoComp->descripcion ?? $comp->tipo;
                        if (!isset($resumenConceptos[$concepto])) {
                            $resumenConceptos[$concepto] = ['count' => 0, 'total' => 0];
                        }
                        $resumenConceptos[$concepto]['count']++;
                        $resumenConceptos[$concepto]['total'] += $comp->monto;
                    }
                @endphp
                @foreach($resumenConceptos as $concepto => $data)
                    <tr>
                        <td class="text-center">{{ $data['count'] }}</td>
                        <td>{{ $concepto }}</td>
                        <td class="text-right">{{ number_format($data['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-center"><strong>{{ $anticipo->comprobantes->count() }}</strong></td>
                    <td><strong>TOTAL GASTOS</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalGastos, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div style="clear: both; margin-top: 20px;">
            <div class="info-row">
                <span class="info-label"><strong>(B) IMPORTE A DEPOSITAR/REEMBOLSAR A:</strong></span>
                <span class="info-value">{{ number_format($totalGastos - $anticipo->importe, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-row">
            <div class="info-row">
                <span class="info-label"><strong>ELABORADO POR:</strong></span>
                <span class="info-value">{{ $anticipo->usuario->name ?? 'N/A' }}, {{ $anticipo->usuario->role ?? 'N/A' }}</span>
            </div>
            <div class="info-row" style="margin-top: 30px;">
                <span class="info-label"><strong>APROBADO POR:</strong></span>
                <span class="info-value">_________________________</span>
            </div>
        </div>
    </div>
</body>
</html>

