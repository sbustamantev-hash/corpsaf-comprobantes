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
            abort(403, 'Solo los administradores de área pueden crear anticipos.');
        }

        $area = Area::with('users')->findOrFail($areaId);
        $user = User::findOrFail($userId);

        if ($admin->area_id !== $area->id || $user->area_id !== $area->id) {
            abort(403, 'Solo puedes gestionar anticipos de tu área.');
        }

        $bancos = \App\Models\Banco::orderBy('descripcion')->get();

        return view('anticipos.create', compact('area', 'user', 'bancos'));
    }

    /**
     * Crear anticipo o reembolso para un usuario
     */
    public function store(Request $request, $areaId, $userId)
    {
        $admin = Auth::user();

        if (!$admin->isAreaAdmin()) {
            abort(403, 'Solo los administradores de área pueden crear anticipos.');
        }

        $area = Area::findOrFail($areaId);
        $user = User::findOrFail($userId);

        if ($admin->area_id !== $area->id) {
            abort(403, 'Solo puedes gestionar anticipos de tu área.');
        }

        if ($user->area_id !== $area->id) {
            return redirect()->route('areas.show', $area->id)
                             ->with('error', 'El usuario no pertenece a esta área.');
        }

        $request->validate([
            'tipo' => 'required|in:anticipo,reembolso',
            'fecha' => 'required|date',
            'banco_id' => 'nullable|exists:bancos,id',
            'ruc' => 'nullable|string|max:20',
            'importe' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string',
        ]);

        Anticipo::create([
            'area_id' => $area->id,
            'user_id' => $user->id,
            'creado_por' => $admin->id,
            'tipo' => $request->tipo,
            'fecha' => $request->fecha,
            'banco_id' => $request->banco_id,
            'ruc' => $request->ruc,
            'importe' => $request->importe,
            'descripcion' => $request->descripcion,
            'estado' => 'pendiente',
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
