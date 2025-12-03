<?php

namespace App\Http\Controllers;

use App\Models\Requerimiento;
use App\Models\Area;
use App\Models\Mensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\Role;

class RequerimientoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isMarketingAdmin()) {
            // Marketing ve todos los requerimientos
            $requerimientos = Requerimiento::with('area')->latest()->get();
        } else {
            // Empresa (Area Admin) ve solo los suyos
            // Asumimos que el usuario tiene area_id asignado si es empresa
            $requerimientos = Requerimiento::where('area_id', $user->area_id)->latest()->get();
        }

        return view('requerimientos.index', compact('requerimientos'));
    }

    public function create()
    {
        // Solo las empresas pueden crear requerimientos?
        // "Las empresas pueden solicitar a Marketing... Todo esto dentro del chat."
        // Si todo es por chat, ¿hay un botón "Nuevo Requerimiento"?
        // Asumiremos que sí, para iniciar el tema.
        return view('requerimientos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'detalle' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Crear requerimiento
        $requerimiento = Requerimiento::create([
            'area_id' => $user->area_id, // El usuario debe tener area_id
            'titulo' => $request->titulo,
            'detalle' => $request->detalle,
            'estado' => 'pendiente',
            'porcentaje_avance' => 0,
            'created_by' => $user->id,
        ]);

        // Crear mensaje inicial si hay detalle
        if ($request->detalle) {
            Mensaje::create([
                'requerimiento_id' => $requerimiento->id,
                'user_id' => $user->id,
                'mensaje' => $request->detalle,
                'tipo' => 'texto',
            ]);
        }

        return redirect()->route('requerimientos.show', $requerimiento);
    }

    public function show(Requerimiento $requerimiento)
    {
        $user = Auth::user();

        // Validar acceso
        if (!$user->isMarketingAdmin() && $user->area_id !== $requerimiento->area_id) {
            abort(403, 'No tienes permiso para ver este requerimiento.');
        }

        $requerimiento->load(['mensajes.user', 'mensajes.archivos', 'area']);

        return view('requerimientos.show', compact('requerimiento'));
    }

    public function updateProgress(Request $request, Requerimiento $requerimiento)
    {
        // Solo Marketing puede actualizar progreso
        if (!Auth::user()->isMarketingAdmin()) {
            abort(403);
        }

        $request->validate([
            'porcentaje_avance' => 'required|integer|min:0|max:100',
        ]);

        $requerimiento->update([
            'porcentaje_avance' => $request->porcentaje_avance,
            'updated_by' => Auth::id(),
        ]);

        if ($request->porcentaje_avance == 100) {
            $requerimiento->update(['estado' => 'completado']);
        } elseif ($request->porcentaje_avance > 0) {
            $requerimiento->update(['estado' => 'en_proceso']);
        }

        return back()->with('success', 'Progreso actualizado.');
    }
}
