<?php

namespace App\Http\Controllers;

use App\Models\Anticipo;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Carbon\Carbon;

class AnticipoController extends Controller
{
    public function create($areaId, $userId)
    {
        $admin = Auth::user();

        if (!$admin->isAreaAdmin()) {
            abort(403, 'Solo los administradores de Empresa pueden crear anticipos.');
        }

        $area = Area::with('users')->findOrFail($areaId);
        $user = User::findOrFail($userId);

        if ($admin->area_id !== $area->id || $user->area_id !== $area->id) {
            abort(403, 'Solo puedes gestionar anticipos de tu Empresa.');
        }

        $bancos = \App\Models\Banco::orderBy('descripcion')->get();
        $tipos_rendicion = \App\Models\TipoRendicion::orderBy('descripcion')->get();

        return view('anticipos.create', compact('area', 'user', 'bancos', 'tipos_rendicion'));
    }

    /**
     * Crear anticipo o reembolso para un usuario
     */
    public function store(Request $request, $areaId, $userId)
    {
        $admin = Auth::user();

        if (!$admin->isAreaAdmin()) {
            abort(403, 'Solo los administradores de Empresa pueden crear anticipos.');
        }

        $area = Area::findOrFail($areaId);
        $user = User::findOrFail($userId);

        if ($admin->area_id !== $area->id) {
            abort(403, 'Solo puedes gestionar anticipos de tu Empresa.');
        }

        if ($user->area_id !== $area->id) {
            return redirect()->route('areas.show', $area->id)
                ->with('error', 'El usuario no pertenece a esta Empresa.');
        }

        $rules = [
            'tipo' => 'required|in:anticipo,reembolso',
            'fecha' => 'required|date',
            'descripcion' => 'required|string',
        ];

        if ($request->tipo === 'anticipo') {
            $rules['banco_id'] = 'required|exists:bancos,id';
            $rules['tipo_rendicion_id'] = 'required|exists:tipos_rendicion,id';
            $rules['importe'] = 'required|numeric|min:0';
        } else {
            // Para reembolso, importe no es requerido (se calcula después)
            $rules['importe'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        Anticipo::create([
            'area_id' => $area->id,
            'user_id' => $user->id,
            'creado_por' => $admin->id,
            'tipo' => $request->tipo,
            'fecha' => $request->fecha,
            'banco_id' => $request->banco_id,
            'TipoRendicion' => $request->TipoRendicion,
            'importe' => $request->tipo === 'reembolso' ? null : $request->importe,
            'descripcion' => $request->descripcion,
            'estado' => 'pendiente',
            'tipo_rendicion_id' => $request->tipo_rendicion_id,
        ]);

        return redirect()->route('comprobantes.index')
            ->with('success', 'Anticipo registrado correctamente.');
    }

    /**
     * Operador sube comprobantes para un anticipo
     */
    public function uploadComprobante(Request $request, $anticipoId)
    {
        $user = Auth::user();
        $anticipo = Anticipo::with('usuario')->findOrFail($anticipoId);

        if ($user->isAdmin()) {
            abort(403, 'El super administrador no puede registrar comprobantes.');
        }

        if ($user->isAreaAdmin()) {
            if ($user->area_id !== $anticipo->area_id) {
                abort(403, 'No tienes permisos para este anticipo.');
            }
        } else {
            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para este anticipo.');
            }
        }

        $validated = $request->validate([
            'tipo' => 'required|exists:tipos_comprobante,codigo',
            'fecha' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'detalle' => 'nullable|string',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:40960',
        ]);

        $archivoPath = $request->file('archivo')->store('comprobantes', ['disk' => 'public']);

        $comprobante = $anticipo->comprobantes()->create([
            'user_id' => $anticipo->user_id,
            'tipo' => $validated['tipo'],
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
            'detalle' => $validated['detalle'] ?? null,
            'archivo' => $archivoPath,
            'estado' => 'pendiente',
        ]);

        // El estado del anticipo solo cambia cuando un admin lo aprueba o rechaza manualmente
        // No se cambia automáticamente aunque se alcance el monto

        return redirect()->back()->with('success', 'Comprobante registrado correctamente.');
    }

    /**
     * Ver detalle del anticipo
     */
    public function show($id)
    {
        $user = Auth::user();
        $anticipo = Anticipo::with([
            'usuario',
            'area',
            'banco',
            'creador',
            'comprobantes.user',
            'comprobantes.observaciones.user'
        ])->findOrFail($id);

        // Verificar permisos
        if ($user->isAreaAdmin()) {
            if ($anticipo->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para ver este anticipo.');
            }
        } elseif ($user->isOperador()) {
            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para ver este anticipo.');
            }
        }

        $totalComprobado = $anticipo->comprobantes->sum('monto');
        $restante = $anticipo->importe - $totalComprobado; // Permite valores negativos
        $porcentaje = $anticipo->importe > 0 ? min(100, ($totalComprobado / $anticipo->importe) * 100) : 0;

        return view('anticipos.show', compact('anticipo', 'totalComprobado', 'restante', 'porcentaje'));
    }

    /**
     * Aprobar anticipo
     */
    public function aprobar(Request $request, $id)
    {
        $user = Auth::user();

        // Solo super admin y area admin pueden aprobar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden aprobar anticipos.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:2',
        ]);

        $anticipo = Anticipo::with('area')->findOrFail($id);

        // Area admin solo puede aprobar anticipos de su Empresa
        if ($user->isAreaAdmin() && $anticipo->area_id !== $user->area_id) {
            abort(403, 'Solo puedes aprobar anticipos de tu Empresa.');
        }

        // Cambiar estado y guardar quien aprobó
        $anticipo->estado = 'aprobado';
        $anticipo->aprobado_por = $user->id;
        $anticipo->save();

        return redirect()->route('anticipos.show', $anticipo->id)
            ->with('success', 'Anticipo aprobado correctamente.');
    }

    /**
     * Rechazar anticipo
     */
    public function rechazar(Request $request, $id)
    {
        $user = Auth::user();

        // Solo super admin y area admin pueden rechazar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden rechazar anticipos.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:2',
        ]);

        $anticipo = Anticipo::with('area')->findOrFail($id);

        // Area admin solo puede rechazar anticipos de su Empresa
        if ($user->isAreaAdmin() && $anticipo->area_id !== $user->area_id) {
            abort(403, 'Solo puedes rechazar anticipos de tu Empresa.');
        }

        // Cambiar estado y guardar quien rechazó
        $anticipo->estado = 'rechazado';
        $anticipo->aprobado_por = $user->id;
        $anticipo->save();

        return redirect()->route('anticipos.show', $anticipo->id)
            ->with('success', 'Anticipo rechazado correctamente.');
    }

    /**
     * Exportar anticipo a PDF
     */
    public function exportPdf($id)
    {
        $user = Auth::user();
        $anticipo = Anticipo::with([
            'usuario',
            'area',
            'banco',
            'creador',
            'aprobador',
            'comprobantes' => function ($query) {
                $query->orderBy('fecha', 'asc');
            }
        ])->findOrFail($id);

        // Autorización
        if ($user->isAdmin()) {
            // Super admin puede ver todo
        } elseif ($user->isAreaAdmin()) {
            if ($anticipo->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para exportar este anticipo.');
            }
        } elseif ($user->isOperador()) {
            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para exportar este anticipo.');
            }
        } else {
            abort(403, 'No tienes permisos para exportar este anticipo.');
        }

        $dompdf = new Dompdf();

        // Generar HTML directamente sin usar Blade
        $html = $this->generatePdfHtml($anticipo);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'liquidacion_' . strtoupper($anticipo->tipo) . '_' . $anticipo->id . '_' . date('Ymd') . '.pdf';

        return $dompdf->stream($filename);
    }

    /**
     * Exportar anticipo a Excel
     */
    public function exportExcel($id)
    {
        $user = Auth::user();
        $anticipo = Anticipo::with([
            'usuario',
            'area',
            'banco',
            'creador',
            'comprobantes' => function ($query) {
                $query->orderBy('fecha', 'asc');
            }
        ])->findOrFail($id);

        // Autorización
        if ($user->isAdmin()) {
            // Super admin puede ver todo
        } elseif ($user->isAreaAdmin()) {
            if ($anticipo->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para exportar este anticipo.');
            }
        } elseif ($user->isOperador()) {
            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para exportar este anticipo.');
            }
        } else {
            abort(403, 'No tienes permisos para exportar este anticipo.');
        }

        // Generar CSV simple en lugar de Excel por ahora
        $filename = 'liquidacion_' . strtoupper($anticipo->tipo) . '_' . $anticipo->id . '_' . date('Ymd') . '.csv';

        $data = $this->generateExcelData($anticipo);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($data as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generar datos para Excel/CSV
     */
    private function generateExcelData($anticipo)
    {
        $data = [];

        // Información del anticipo
        $data[] = ['LIQUIDACION DE GASTOS POR:', strtoupper($anticipo->tipo == 'anticipo' ? 'ANTICIPO' : 'REEMBOLSO')];
        $data[] = [];
        $data[] = ['EMPRESA:', $anticipo->area->nombre ?? 'N/A'];
        $data[] = ['NOMBRE SOLICITANTE:', $anticipo->usuario->name ?? 'N/A'];
        $data[] = ['CARGO:', $anticipo->usuario->role ?? 'N/A'];
        $fechaAnticipo = $anticipo->fecha instanceof \Carbon\Carbon
            ? $anticipo->fecha
            : Carbon::parse($anticipo->fecha);
        $data[] = ['FECHA DE SOLICITUD:', $fechaAnticipo->format('d/m/Y')];
        $maxFecha = $anticipo->comprobantes->max('fecha');
        $fechaRendicion = $maxFecha ? (is_string($maxFecha) ? Carbon::parse($maxFecha) : $maxFecha)->format('d/m/Y') : 'N/A';
        $data[] = ['FECHA RENDICIÓN:', $fechaRendicion];
        if ($anticipo->banco) {
            $data[] = ['BANCO:', $anticipo->banco->descripcion];
        }
        $data[] = [];
        $data[] = ['IMPORTE ASIGNADO:', number_format($anticipo->importe, 2)];
        $data[] = [];

        // Encabezados de detalle
        $data[] = ['FECHA', 'TIPO DOCUMENTO', 'N° DOCUMENTO', 'DESCRIPCIÓN', 'HABER', 'SALDO'];

        // Detalle de comprobantes
        $saldo = 0;
        foreach ($anticipo->comprobantes->sortBy('fecha') as $comprobante) {
            $tipoComprobante = \App\Models\TipoComprobante::where('codigo', $comprobante->tipo)->first();
            $saldo += $comprobante->monto;
            $fechaComprobante = $comprobante->fecha instanceof \Carbon\Carbon
                ? $comprobante->fecha
                : Carbon::parse($comprobante->fecha);
            $data[] = [
                $fechaComprobante->format('d/m/Y'),
                $tipoComprobante->descripcion ?? $comprobante->tipo,
                $comprobante->id,
                $comprobante->detalle ?? 'GASTO VARIOS',
                number_format($comprobante->monto, 2),
                number_format($saldo, 2)
            ];
        }

        // Total
        $totalGastos = $anticipo->comprobantes->sum('monto');
        $data[] = ['TOTALES', '', '', '', number_format($totalGastos, 2), number_format($saldo, 2)];
        $data[] = [];
        $data[] = ['IMPORTE RECIBIDO:', number_format($anticipo->importe, 2)];
        $data[] = ['TOTAL GASTOS:', number_format($totalGastos, 2)];
        $data[] = ['IMPORTE A DEPOSITAR/REEMBOLSAR:', number_format($totalGastos - $anticipo->importe, 2)];

        return $data;
    }

    /**
     * Generar HTML para PDF directamente
     */
    private function generatePdfHtml($anticipo)
    {
        $totalGastos = $anticipo->comprobantes->sum('monto');
        $saldo = 0;

        // Resumen por conceptos
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

        $fechaRendicion = $anticipo->comprobantes->max('fecha') ? \Carbon\Carbon::parse($anticipo->comprobantes->max('fecha'))->format('d/m/Y') : 'N/A';

        $html = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .header h2 { font-size: 14px; font-weight: bold; margin-bottom: 10px; }
        .info-section { margin-bottom: 15px; }
        .info-row { display: flex; margin-bottom: 5px; font-size: 9px; }
        .info-label { font-weight: bold; width: 150px; }
        .info-value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 8px; }
        table th { background-color: #f0f0f0; border: 1px solid #000; padding: 4px; text-align: left; font-weight: bold; }
        table td { border: 1px solid #000; padding: 4px; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f0f0f0; }
        </style></head><body>';

        $html .= '<div class="header"><h1>LIQUIDACION DE GASTOS POR:</h1><h2>' . strtoupper($anticipo->tipo == 'anticipo' ? 'ANTICIPO' : 'REEMBOLSO') . '</h2></div>';

        $html .= '<div class="info-section">
            <div class="info-row"><span class="info-label">EMPRESA:</span><span class="info-value">' . ($anticipo->area->nombre ?? 'N/A') . '</span></div>
            <div class="info-row"><span class="info-label">NOMBRE SOLICITANTE:</span><span class="info-value">' . ($anticipo->usuario->name ?? 'N/A') . '</span></div>
            <div class="info-row"><span class="info-label">CARGO:</span><span class="info-value">' . ($anticipo->usuario->role ?? 'N/A') . '</span></div>
            <div class="info-row"><span class="info-label">FECHA DE SOLICITUD:</span><span class="info-value">' . ($anticipo->fecha instanceof \Carbon\Carbon ? $anticipo->fecha : Carbon::parse($anticipo->fecha))->format('d/m/Y') . '</span></div>
            <div class="info-row"><span class="info-label">FECHA RENDICIÓN:</span><span class="info-value">' . $fechaRendicion . '</span></div>';
        if ($anticipo->banco) {
            $html .= '<div class="info-row"><span class="info-label">BANCO:</span><span class="info-value">' . $anticipo->banco->descripcion . '</span></div>';
        }
        $html .= '</div>';

        // Tabla de asignación
        $html .= '<table><thead><tr><th>RICHA</th><th>TIPO DOCUMENTO</th><th>N° CUENTA</th><th>BANCO</th><th>CONCEPTO</th><th class="text-right">IMPORTE</th></tr></thead><tbody>
            <tr><td>' . strtoupper($anticipo->tipo) . '</td><td>-</td><td>-</td><td>' . ($anticipo->banco->descripcion ?? '-') . '</td><td>' . ($anticipo->descripcion ?? 'ANTICIPO/REEMBOLSO') . '</td><td class="text-right">' . number_format($anticipo->importe, 2) . '</td></tr>
        </tbody></table>';

        // Tabla de detalle
        $html .= '<table><thead><tr><th>FECHA</th><th>TIPO DOCUMENTO</th><th>N° DOCUMENTO</th><th>DENOMINACIÓN / RAZÓN SOCIAL</th><th>DESCRIPCIÓN</th><th class="text-right">HABER</th><th class="text-right">SALDO</th></tr></thead><tbody>';
        foreach ($anticipo->comprobantes->sortBy('fecha') as $comprobante) {
            $tipoComprobante = \App\Models\TipoComprobante::where('codigo', $comprobante->tipo)->first();
            $saldo += $comprobante->monto;
            $fechaComprobante = $comprobante->fecha instanceof \Carbon\Carbon
                ? $comprobante->fecha
                : Carbon::parse($comprobante->fecha);
            $html .= '<tr>
                <td>' . $fechaComprobante->format('d/m/Y') . '</td>
                <td>' . ($tipoComprobante->descripcion ?? $comprobante->tipo) . '</td>
                <td>' . $comprobante->id . '</td>
                <td>' . ($comprobante->detalle ?? '-') . '</td>
                <td>' . ($comprobante->detalle ?? 'GASTO VARIOS') . '</td>
                <td class="text-right">' . number_format($comprobante->monto, 2) . '</td>
                <td class="text-right">(' . number_format($saldo, 2) . ')</td>
            </tr>';
        }
        $html .= '<tr class="total-row"><td colspan="5" class="text-right"><strong>TOTALES</strong></td><td class="text-right"><strong>' . number_format($totalGastos, 2) . '</strong></td><td class="text-right"><strong>(' . number_format($saldo, 2) . ')</strong></td></tr></tbody></table>';

        // Resumen
        $html .= '<div class="info-section"><div class="info-row"><span class="info-label"><strong>(A) IMPORTE RECIBIDO:</strong></span><span class="info-value">' . number_format($anticipo->importe, 2) . '</span></div></div>';

        $html .= '<table style="width: 50%;"><thead><tr><th>N° COMPROB.</th><th>CONCEPTOS</th><th class="text-right">IMPORTE</th></tr></thead><tbody>';
        foreach ($resumenConceptos as $concepto => $data) {
            $html .= '<tr><td class="text-center">' . $data['count'] . '</td><td>' . $concepto . '</td><td class="text-right">' . number_format($data['total'], 2) . '</td></tr>';
        }
        $html .= '<tr class="total-row"><td class="text-center"><strong>' . $anticipo->comprobantes->count() . '</strong></td><td><strong>TOTAL GASTOS</strong></td><td class="text-right"><strong>' . number_format($totalGastos, 2) . '</strong></td></tr></tbody></table>';

        $html .= '<div class="info-row" style="margin-top: 20px;"><span class="info-label"><strong>(B) IMPORTE A DEPOSITAR/REEMBOLSAR A:</strong></span><span class="info-value">' . number_format($totalGastos - $anticipo->importe, 2) . '</span></div>';


        $html .= '<div style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #000;">
            <div class="info-row"><span class="info-label"><strong>ELABORADO POR:</strong></span><span class="info-value">' . ($anticipo->usuario->name ?? 'N/A') . ', ' . ($anticipo->usuario->role ?? 'N/A') . '</span></div>';

        // Dynamic footer based on status
        if ($anticipo->estado === 'aprobado') {
            $aprobadorName = $anticipo->aprobador->name ?? '_________________________';
            $html .= '<div class="info-row" style="margin-top: 30px;"><span class="info-label"><strong>APROBADO POR:</strong></span><span class="info-value">' . $aprobadorName . '</span></div>';
        } elseif ($anticipo->estado === 'rechazado') {
            $rechazadorName = $anticipo->aprobador->name ?? '_________________________';
            $html .= '<div class="info-row" style="margin-top: 30px;"><span class="info-label"><strong>RECHAZADO POR:</strong></span><span class="info-value">' . $rechazadorName . '</span></div>';
        } else {
            $html .= '<div class="info-row" style="margin-top: 30px;"><span class="info-label"><strong>ESTADO:</strong></span><span class="info-value">PENDIENTE DE APROBACIÓN</span></div>';
        }

        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Descargar archivo adjunto de anticipo (si se agregan archivos)
     */
    public function downloadArchivo($id)
    {
        $anticipo = Anticipo::findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin()) {
            if ($user->isAreaAdmin() && $user->area_id !== $anticipo->area_id) {
                abort(403, 'No tienes permisos para este anticipo.');
            }

            if ($user->isOperador() && $anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para este anticipo.');
            }
        }

        if (!$anticipo->archivo || !Storage::disk('public')->exists($anticipo->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($anticipo->archivo);
    }
}
