<?php

namespace App\Http\Controllers;

use App\Models\Anticipo;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $request->validate([
            'tipo' => 'required|in:anticipo,reembolso',
            'fecha' => 'required|date',
            'banco_id' => 'nullable|exists:bancos,id',
            'TipoRendicion' => 'nullable|string|max:20',
            'importe' => 'required_if:tipo,anticipo|nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'tipo_rendicion_id' => 'nullable|exists:tipos_rendicion,id',
        ]);

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
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
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

        // Si el monto total alcanza el anticipo, marcar como completo
        $totalComprobado = $anticipo->comprobantes()->sum('monto');
        if ($totalComprobado >= $anticipo->importe) {
            $anticipo->estado = 'completo';
            $anticipo->save();
        }

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
        $restante = max(0, $anticipo->importe - $totalComprobado);
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
            'mensaje' => 'required|string|min:10',
        ]);

        $anticipo = Anticipo::with('area')->findOrFail($id);

        // Area admin solo puede aprobar anticipos de su Empresa
        if ($user->isAreaAdmin() && $anticipo->area_id !== $user->area_id) {
            abort(403, 'Solo puedes aprobar anticipos de tu Empresa.');
        }

        // Cambiar estado
        $anticipo->estado = 'aprobado';
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
            'mensaje' => 'required|string|min:10',
        ]);

        $anticipo = Anticipo::with('area')->findOrFail($id);

        // Area admin solo puede rechazar anticipos de su Empresa
        if ($user->isAreaAdmin() && $anticipo->area_id !== $user->area_id) {
            abort(403, 'Solo puedes rechazar anticipos de tu Empresa.');
        }

        // Cambiar estado
        $anticipo->estado = 'rechazado';
        $anticipo->save();

        return redirect()->route('anticipos.show', $anticipo->id)
            ->with('success', 'Anticipo rechazado correctamente.');
    }

    /**
     * Exportar anticipo a PDF
     */
    public function exportPdf($id)
    {
        // TODO: Implementar exportación a PDF cuando se pase el formato
        $anticipo = Anticipo::with(['usuario', 'area', 'banco', 'comprobantes'])->findOrFail($id);

        return response()->json([
            'message' => 'Exportación a PDF pendiente de implementar',
            'anticipo' => $anticipo
        ]);
    }

    /**
     * Exportar anticipo a Excel
     */
    public function exportExcel($id)
    {
        // TODO: Implementar exportación a Excel cuando se pase el formato
        $anticipo = Anticipo::with(['usuario', 'area', 'banco', 'comprobantes'])->findOrFail($id);

        return response()->json([
            'message' => 'Exportación a Excel pendiente de implementar',
            'anticipo' => $anticipo
        ]);
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
