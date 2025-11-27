<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
</head>

<body>
    <table>
        <tr>
            <td colspan="6" style="font-weight: bold; font-size: 16px; text-align: center;">LIQUIDACION DE GASTOS POR:
            </td>
        </tr>
        <tr>
            <td colspan="6" style="font-weight: bold; font-size: 14px; text-align: center;">
                {{ strtoupper($anticipo->tipo == 'anticipo' ? 'ANTICIPO' : 'REEMBOLSO') }}</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">
                @if($anticipo->usuario->isAreaAdmin() || $anticipo->usuario->isAdmin())
                    ADMINISTRADOR DE EMPRESA:
                @else
                    USUARIO/TRABAJADOR:
                @endif
            </td>
            <td colspan="4">{{ $anticipo->area->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">NOMBRE SOLICITANTE:</td>
            <td colspan="4">{{ $anticipo->usuario->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">CARGO:</td>
            <td colspan="4">{{ $anticipo->usuario->role ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">FECHA DE SOLICITUD:</td>
            <td colspan="4">{{ $anticipo->fecha->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="2" style="font-weight: bold;">FECHA RENDICIÓN:</td>
            <td colspan="4">
                {{ $anticipo->comprobantes->max('fecha') ? \Carbon\Carbon::parse($anticipo->comprobantes->max('fecha'))->format('d/m/Y') : 'N/A' }}
            </td>
        </tr>
        @if($anticipo->banco)
            <tr>
                <td colspan="2" style="font-weight: bold;">BANCO:</td>
                <td colspan="4">{{ $anticipo->banco->descripcion }}</td>
            </tr>
        @endif
        <tr>
            <td></td>
        </tr>

        <!-- Tabla de Asignación -->
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">RICHA</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">TIPO DOCUMENTO</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">N° CUENTA</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">BANCO</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">CONCEPTO</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0; text-align: right;">
                IMPORTE</th>
        </tr>
        <tr>
            <td style="border: 1px solid #000000;">{{ strtoupper($anticipo->tipo) }}</td>
            <td style="border: 1px solid #000000;">-</td>
            <td style="border: 1px solid #000000;">-</td>
            <td style="border: 1px solid #000000;">{{ $anticipo->banco->descripcion ?? '-' }}</td>
            <td style="border: 1px solid #000000;">{{ $anticipo->descripcion ?? 'ANTICIPO/REEMBOLSO' }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ number_format($anticipo->importe, 2) }}</td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <!-- Tabla de Detalle -->
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">FECHA</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">TIPO DOCUMENTO</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">N° DOCUMENTO</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">DENOMINACIÓN / RAZÓN
                SOCIAL</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">DESCRIPCIÓN</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0; text-align: right;">
                HABER</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0; text-align: right;">
                SALDO</th>
        </tr>
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
                <td style="border: 1px solid #000000;">{{ $comprobante->fecha->format('d/m/Y') }}</td>
                <td style="border: 1px solid #000000;">{{ $tipoComprobante->descripcion ?? $comprobante->tipo }}</td>
                <td style="border: 1px solid #000000;">{{ $comprobante->id }}</td>
                <td style="border: 1px solid #000000;">{{ $comprobante->detalle ?? '-' }}</td>
                <td style="border: 1px solid #000000;">{{ $comprobante->detalle ?? 'GASTO VARIOS' }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ number_format($comprobante->monto, 2) }}</td>
                <td style="border: 1px solid #000000; text-align: right;">({{ number_format($saldo, 2) }})</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5"
                style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #f0f0f0;">
                TOTALES</td>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #f0f0f0;">
                {{ number_format($totalGastos, 2) }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #f0f0f0;">
                ({{ number_format($saldo, 2) }})</td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <!-- Resumen -->
        <tr>
            <td colspan="2" style="font-weight: bold;">(A) IMPORTE RECIBIDO:</td>
            <td style="text-align: right;">{{ number_format($anticipo->importe, 2) }}</td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0; text-align: center;">N°
                COMPROB.</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">CONCEPTOS
            </th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0; text-align: right;">
                IMPORTE</th>
        </tr>
        @php
            $resumenConceptos = [];
            foreach ($anticipo->comprobantes as $comp) {
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
                <td style="border: 1px solid #000000; text-align: center;">{{ $data['count'] }}</td>
                <td colspan="2" style="border: 1px solid #000000;">{{ $concepto }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ number_format($data['total'], 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #f0f0f0;">
                {{ $anticipo->comprobantes->count() }}</td>
            <td colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #f0f0f0;">TOTAL
                GASTOS</td>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #f0f0f0;">
                {{ number_format($totalGastos, 2) }}</td>
        </tr>
        <tr>
            <td></td>
        </tr>

        <tr>
            <td colspan="3" style="font-weight: bold;">(B) IMPORTE A DEPOSITAR/REEMBOLSAR A:</td>
            <td style="text-align: right;">{{ number_format($anticipo->importe - $totalGastos, 2) }}</td>
        </tr>
    </table>
</body>

</html>