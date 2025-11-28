<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\User;
use App\Models\Observacion;
use App\Models\Anticipo;
use App\Models\TipoComprobante;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ComprobanteController extends Controller
{
    // LISTAR COMPROBANTES
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $anticipos = collect();
        $tiposComprobante = collect();
        $operadores = collect();
        $comprobantes = collect();

        // Super admin: no mostrar comprobantes, mostrar info general del sistema
        if ($user->isAdmin()) {
            // No cargar comprobantes para super admin
            // Se pasarán estadísticas generales a la vista
        }
        // Area admin: ver solo comprobantes de su Empresa
        elseif ($user->isAreaAdmin()) {
            $comprobantes = Comprobante::with('user.area')
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('area_id', $user->area_id);
                })
                ->orderBy('id', 'desc')
                ->get();

            $operadores = User::where('area_id', $user->area_id)
                ->where('id', '!=', $user->id)
                ->whereIn('role', ['operador', 'trabajador'])
                ->orderBy('name')
                ->get();

            $anticipos = Anticipo::with(['banco', 'creador', 'usuario'])
                ->where('area_id', $user->area_id)
                ->orderBy('fecha', 'desc')
                ->get();
        }
        // Operador: ver solo sus propios comprobantes
        else {
            $comprobantes = Comprobante::with('user.area')
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->get();

            $anticipos = Anticipo::with(['banco', 'creador'])
                ->where('user_id', $user->id)
                ->orderBy('fecha', 'desc')
                ->get();

            $tiposComprobante = TipoComprobante::orderBy('descripcion')->get();
        }
        
        // Para super admin, pasar estadísticas generales
        if ($user->isAdmin()) {
            $totalAreas = Area::count();
            $areasActivas = Area::where('activo', true)->count();
            $totalUsuarios = User::where('id', '!=', $user->id)->count();
            $totalAreaAdmins = User::where('role', 'area_admin')->count();
            $totalOperadores = User::whereIn('role', ['operador', 'trabajador'])->count();
            
            return view('comprobantes.index', compact(
                'comprobantes', 
                'anticipos', 
                'tiposComprobante', 
                'operadores',
                'totalAreas',
                'areasActivas',
                'totalUsuarios',
                'totalAreaAdmins',
                'totalOperadores'
            ));
        }
        
        return view('comprobantes.index', compact('comprobantes', 'anticipos', 'tiposComprobante', 'operadores'));
    }

    // FORMULARIO CREAR
    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo operadores pueden crear comprobantes
        if (!$user->isOperador()) {
            abort(403, 'Solo los operadores pueden crear comprobantes.');
        }

        $anticipo = null;
        if ($request->filled('anticipo_id')) {
            $anticipo = Anticipo::with('usuario')->findOrFail($request->anticipo_id);

            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No tienes permisos para subir comprobantes de este anticipo.');
            }

            // No permitir crear comprobantes si el anticipo está aprobado o rechazado
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes subir comprobantes a un anticipo que ha sido aprobado o rechazado.');
            }
        }

        $tiposComprobante = TipoComprobante::where('activo', true)
            ->orderBy('descripcion')
            ->get();

        return view('comprobantes.create', compact('anticipo', 'tiposComprobante'));
    }

    // GUARDAR EN BD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|exists:tipos_comprobante,codigo',
            'serie' => ['required', 'alpha_num', 'max:4'],
            'numero' => ['required', 'regex:/^\d{1,10}$/'],
            'monto' => 'required|numeric',
            'fecha' => 'required|date',
            'detalle' => 'nullable|string',
            // Máximo 100 MB (100 * 1024 KB)
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:102400',
            'anticipo_id' => 'nullable|exists:anticipos,id'
        ]);

        $archivoPath = null;

        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('comprobantes', ['disk' => 'public']);
        }

        // Asignar al usuario autenticado
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $anticipo = null;
        if (!empty($validated['anticipo_id'])) {
            $anticipo = Anticipo::with('comprobantes')->findOrFail($validated['anticipo_id']);

            if ($anticipo->user_id !== $user->id) {
                abort(403, 'No puedes registrar comprobantes para este anticipo.');
            }

            // No permitir crear comprobantes si el anticipo está aprobado o rechazado
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes subir comprobantes a un anticipo que ha sido aprobado o rechazado.');
            }
        }

        $serie = str_pad(strtoupper($validated['serie']), 4, '0', STR_PAD_LEFT);
        $numero = str_pad($validated['numero'], 10, '0', STR_PAD_LEFT);

        $comprobante = Comprobante::create([
            'user_id' => $user->id,
            'anticipo_id' => $anticipo?->id,
            'tipo' => $validated['tipo'],
            'serie' => $serie,
            'numero' => $numero,
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
            'detalle' => $validated['detalle'] ?? null,
            'archivo' => $archivoPath,
            'estado' => 'pendiente'
        ]);

        // El estado del anticipo solo cambia cuando un admin lo aprueba o rechaza manualmente
        // No se cambia automáticamente aunque se alcance el monto

        return redirect()->route('comprobantes.index')
            ->with('success', 'Comprobante registrado correctamente.');
    }

    // VER DETALLES
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::with(['user.area', 'observaciones.user'])->findOrFail($id);

        // Super admin: puede ver todo
        if ($user->isAdmin()) {
            return view('comprobantes.show', compact('comprobante'));
        }

        // Area admin: solo puede ver comprobantes de su Empresa
        if ($user->isAreaAdmin()) {
            if ($comprobante->user->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para ver este comprobante.');
            }
            return view('comprobantes.show', compact('comprobante'));
        }

        // Operador: solo puede ver sus propios comprobantes
        if ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este comprobante.');
        }

        return view('comprobantes.show', compact('comprobante'));
    }

    // FORMULARIO EDITAR
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::with('user')->findOrFail($id);

        // Solo operadores pueden editar
        if (!$user->isOperador()) {
            abort(403, 'Solo los operadores pueden editar comprobantes.');
        }

        // Solo puede editar sus propios comprobantes
        if ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este comprobante.');
        }

        // No permitir edición si el comprobante ya fue aprobado o rechazado
        if (in_array($comprobante->estado, ['aprobado', 'rechazado'])) {
            abort(403, 'No puedes modificar un comprobante aprobado o rechazado.');
        }

        // No permitir edición si el anticipo asociado está aprobado o rechazado
        if ($comprobante->anticipo) {
            if (in_array($comprobante->anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar comprobantes de un anticipo que ha sido aprobado o rechazado.');
            }
        }

        return view('comprobantes.edit', compact('comprobante'));
    }

    // ACTUALIZAR BD
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string|max:50',
            'serie' => ['required', 'alpha_num', 'max:4'],
            'numero' => ['required', 'regex:/^\d{1,10}$/'],
            'monto' => 'required|numeric',
            'fecha' => 'required|date',
            'detalle' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);

        // Si es operador, solo puede actualizar sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para actualizar este comprobante.');
        }

        // No permitir edición si el comprobante ya fue aprobado o rechazado
        if (in_array($comprobante->estado, ['aprobado', 'rechazado'])) {
            abort(403, 'No puedes modificar un comprobante aprobado o rechazado.');
        }

        // No permitir edición si el anticipo asociado está aprobado o rechazado
        if ($comprobante->anticipo) {
            if (in_array($comprobante->anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar comprobantes de un anticipo que ha sido aprobado o rechazado.');
            }
        }

        if ($request->hasFile('archivo')) {
            // Borrar archivo anterior si existe
            if ($comprobante->archivo) {
                Storage::disk('public')->delete($comprobante->archivo);
            }
            $archivoPath = $request->file('archivo')->store('comprobantes', ['disk' => 'public']);
            $comprobante->archivo = $archivoPath;
        }

        $comprobante->tipo = $request->tipo;
        $comprobante->serie = str_pad(strtoupper($request->serie), 4, '0', STR_PAD_LEFT);
        $comprobante->numero = str_pad($request->numero, 10, '0', STR_PAD_LEFT);
        $comprobante->monto = $request->monto;
        $comprobante->fecha = $request->fecha;
        $comprobante->detalle = $request->detalle;

        $comprobante->save();

        return redirect()->route('comprobantes.index')
            ->with('success', 'Comprobante actualizado correctamente.');
    }

    // ELIMINAR
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);

        // Si es operador, solo puede eliminar sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para eliminar este comprobante.');
        }

        // Borrar archivo si existe
        if ($comprobante->archivo) {
            Storage::disk('public')->delete($comprobante->archivo);
        }

        $comprobante->delete();

        return redirect()->route('comprobantes.index')
            ->with('success', 'Comprobante eliminado correctamente.');
    }

    // SERVIR ARCHIVO CON AUTENTICACIÓN
    public function download($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::with('user')->findOrFail($id);

        // Super admin: puede ver todos
        if ($user->isAdmin()) {
            // Permitir
        }
        // Area admin: solo de su Empresa
        elseif ($user->isAreaAdmin()) {
            if ($comprobante->user->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para ver este archivo.');
            }
        }
        // Operador: solo los suyos
        elseif ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este archivo.');
        }

        if (!$comprobante->archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $filePath = storage_path('app/public/' . $comprobante->archivo);

        if (!file_exists($filePath)) {
            abort(404, 'Archivo no encontrado en el servidor.');
        }

        return response()->file($filePath);
    }

    // APROBAR COMPROBANTE (super admin y area admin)
    public function aprobar(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo super admin y area admin pueden aprobar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden aprobar comprobantes.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:10',
        ]);

        $comprobante = Comprobante::with('user')->findOrFail($id);

        // Area admin solo puede aprobar comprobantes de su Empresa
        if ($user->isAreaAdmin() && $comprobante->user->area_id !== $user->area_id) {
            abort(403, 'Solo puedes aprobar comprobantes de tu Empresa.');
        }

        // Cambiar estado
        $comprobante->estado = 'aprobado';
        $comprobante->save();

        // Crear observación de aprobación
        Observacion::create([
            'comprobante_id' => $comprobante->id,
            'user_id' => $user->id,
            'mensaje' => $request->mensaje,
            'tipo' => 'aprobacion',
        ]);

        return redirect()->route('comprobantes.show', $comprobante->id)
            ->with('success', 'Comprobante aprobado correctamente.');
    }

    // RECHAZAR COMPROBANTE (super admin y area admin)
    public function rechazar(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo super admin y area admin pueden rechazar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden rechazar comprobantes.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:10',
        ]);

        $comprobante = Comprobante::with('user')->findOrFail($id);

        // Area admin solo puede rechazar comprobantes de su Empresa
        if ($user->isAreaAdmin() && $comprobante->user->area_id !== $user->area_id) {
            abort(403, 'Solo puedes rechazar comprobantes de tu Empresa.');
        }

        // Cambiar estado
        $comprobante->estado = 'rechazado';
        $comprobante->save();

        // Crear observación de rechazo
        Observacion::create([
            'comprobante_id' => $comprobante->id,
            'user_id' => $user->id,
            'mensaje' => $request->mensaje,
            'tipo' => 'rechazo',
        ]);

        return redirect()->route('comprobantes.show', $comprobante->id)
            ->with('success', 'Comprobante rechazado correctamente.');
    }

    // AGREGAR OBSERVACIÓN (cualquier usuario autenticado con acceso)
    public function agregarObservacion(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::with('user')->findOrFail($id);

        // Super admin: puede agregar a cualquier comprobante
        if ($user->isAdmin()) {
            // Permitir
        }
        // Area admin: solo a comprobantes de su Empresa
        elseif ($user->isAreaAdmin()) {
            if ($comprobante->user->area_id !== $user->area_id) {
                abort(403, 'No tienes permisos para agregar observaciones a este comprobante.');
            }
        }
        // Operador: solo a sus propios comprobantes
        elseif ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para agregar observaciones a este comprobante.');
        }

        $request->validate([
            'mensaje' => 'nullable|string|min:5',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        // Al menos mensaje o archivo debe estar presente
        if (empty($request->mensaje) && !$request->hasFile('archivo')) {
            return redirect()->back()
                ->withErrors(['mensaje' => 'Debe proporcionar un mensaje o un archivo.'])
                ->withInput();
        }

        $archivoPath = null;
        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('observaciones', ['disk' => 'public']);
        }

        Observacion::create([
            'comprobante_id' => $comprobante->id,
            'user_id' => $user->id,
            'mensaje' => $request->mensaje ?? '',
            'tipo' => 'observacion',
            'archivo' => $archivoPath,
        ]);

        // Si un administrador deja una observación, marcar el comprobante como "en_observacion"
        if (($user->isAdmin() || $user->isAreaAdmin()) && in_array($comprobante->estado, ['pendiente', 'rechazado'])) {
            $comprobante->estado = 'en_observacion';
            $comprobante->save();
        }

        return redirect()->route('comprobantes.show', $comprobante->id)
            ->with('success', 'Observación agregada correctamente.');
    }

    // DESCARGAR ARCHIVO DE OBSERVACIÓN
    public function downloadObservacion($id)
    {
        $observacion = Observacion::findOrFail($id);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar que el usuario tenga acceso al comprobante
        $comprobante = $observacion->comprobante;
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este archivo.');
        }

        if (!$observacion->archivo || !Storage::disk('public')->exists($observacion->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        $path = Storage::disk('public')->path($observacion->archivo);
        return response()->download($path, basename($observacion->archivo));
    }
}
