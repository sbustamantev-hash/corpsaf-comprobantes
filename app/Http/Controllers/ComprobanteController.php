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

        // Calcular estadísticas para Area Admin basadas en Anticipos
        if ($user->isAreaAdmin()) {
            // Sobrescribir las variables que se usan en la vista para las tarjetas
            // La vista usa $pendientes, $aprobados, $rechazados, $total
            // Pero estas variables se calculan en la vista actualmente.
            // Vamos a pasarlas explícitamente para asegurar que se usen las de anticipos.

            $pendientes = $anticipos->whereIn('estado', ['pendiente', 'en_observacion'])->count();
            $aprobados = $anticipos->where('estado', 'aprobado')->count();
            $rechazados = $anticipos->where('estado', 'rechazado')->count();
            $total = $anticipos->count();

            return view('comprobantes.index', compact(
                'comprobantes',
                'anticipos',
                'tiposComprobante',
                'operadores',
                'pendientes',
                'aprobados',
                'rechazados',
                'total'
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

            // Verificar si el anticipo está bloqueado
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes agregar comprobantes a un anticipo aprobado o rechazado.');
            }
        }

        $tiposComprobante = TipoComprobante::where('activo', true)
            ->orderBy('codigo')
            ->get();

        $conceptos = \App\Models\Concepto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('comprobantes.create', compact('anticipo', 'tiposComprobante', 'conceptos'));
    }

    // GUARDAR EN BD
    public function store(Request $request)
    {
        $rules = [
            'ruc_empresa' => 'required|string|max:20',
            'tipo' => 'required|exists:tipos_comprobante,codigo',
            'concepto' => 'required|exists:conceptos,id',
            'serie' => ['required', 'alpha_num', 'max:4'],
            'numero' => ['required', 'regex:/^\d{1,10}$/'],
            'monto' => 'required|numeric',
            'fecha' => 'required|date',
            'detalle' => 'required|string',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:40960',
            'anticipo_id' => 'nullable|exists:anticipos,id'
        ];

        // Si el concepto es "otros", el campo concepto_otro es obligatorio
        $concepto = \App\Models\Concepto::find($request->concepto);
        if ($concepto && strtoupper($concepto->nombre) === 'OTROS') {
            $rules['concepto_otro'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

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

            // Verificar si el anticipo está bloqueado
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes agregar comprobantes a un anticipo aprobado o rechazado.');
            }
        }

        $serie = str_pad(strtoupper($validated['serie']), 4, '0', STR_PAD_LEFT);
        $numero = str_pad($validated['numero'], 10, '0', STR_PAD_LEFT);

        $comprobante = Comprobante::create([
            'user_id' => $user->id,
            'anticipo_id' => $anticipo?->id,
            'tipo' => $validated['tipo'],
            'concepto_id' => $validated['concepto'],
            'concepto_otro' => $validated['concepto_otro'] ?? null,
            'serie' => $serie,
            'numero' => $numero,
            'ruc_empresa' => $validated['ruc_empresa'],
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
            'detalle' => $validated['detalle'] ?? null,
            'archivo' => $archivoPath,
        ]);

        // Si se agregó un nuevo comprobante a un anticipo que estaba "aprobado",
        // cambiar el estado del anticipo a "pendiente" porque ahora hay un nuevo comprobante
        if ($anticipo && $anticipo->estado === 'aprobado') {
            $anticipo->estado = 'pendiente';
            $anticipo->save();
        }

        return redirect()->route('comprobantes.index')
            ->with('success', 'Comprobante registrado correctamente.');
    }

    // VER DETALLES
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::with(['user.area', 'observaciones.user', 'concepto'])->findOrFail($id);

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
        $comprobante = Comprobante::with(['user', 'concepto'])->findOrFail($id);

        // Solo operadores pueden editar
        if (!$user->isOperador()) {
            abort(403, 'Solo los operadores pueden editar comprobantes.');
        }

        // Solo puede editar sus propios comprobantes
        if ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este comprobante.');
        }

        // No permitir edición si el anticipo asociado está aprobado o rechazado
        if ($comprobante->anticipo_id) {
            $anticipo = $comprobante->anticipo;
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar comprobantes de un anticipo aprobado o rechazado.');
            }
        }

        $conceptos = \App\Models\Concepto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('comprobantes.edit', compact('comprobante', 'conceptos'));
    }

    // ACTUALIZAR BD
    public function update(Request $request, $id)
    {
        $rules = [
            'tipo' => 'required|string|max:50',
            'concepto' => 'required|exists:conceptos,id',
            'ruc_empresa' => 'required|string|max:20',
            'serie' => ['required', 'alpha_num', 'max:4'],
            'numero' => ['required', 'regex:/^\d{1,10}$/'],
            'monto' => 'required|numeric',
            'fecha' => 'required|date',
            'detalle' => 'required|string',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:40960'
        ];

        // Si el concepto es "otros", el campo concepto_otro es obligatorio
        $concepto = \App\Models\Concepto::find($request->concepto);
        if ($concepto && strtoupper($concepto->nombre) === 'OTROS') {
            $rules['concepto_otro'] = 'required|string|max:255';
        }

        $request->validate($rules);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);

        // Si es operador, solo puede actualizar sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para actualizar este comprobante.');
        }

        // No permitir edición si el anticipo asociado está aprobado o rechazado
        if ($comprobante->anticipo_id) {
            $anticipo = $comprobante->anticipo;
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar comprobantes de un anticipo aprobado o rechazado.');
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

        // Obtener el concepto para verificar si es "OTROS"
        $conceptoModel = \App\Models\Concepto::findOrFail($request->concepto);

        $comprobante->tipo = $request->tipo;
        $comprobante->concepto_id = $request->concepto;
        $comprobante->concepto_otro = strtoupper($conceptoModel->nombre) === 'OTROS' ? $request->concepto_otro : null;
        $comprobante->serie = str_pad(strtoupper($request->serie), 4, '0', STR_PAD_LEFT);
        $comprobante->numero = str_pad($request->numero, 10, '0', STR_PAD_LEFT);
        $comprobante->ruc_empresa = $request->ruc_empresa;
        $comprobante->monto = $request->monto;
        $comprobante->fecha = $request->fecha;
        $comprobante->detalle = $request->detalle;

        $comprobante->save();

        // Si el anticipo estaba "aprobado" o "en_observacion" y se editó un comprobante, cambiar el anticipo a "pendiente"
        if ($comprobante->anticipo_id) {
            $anticipo = $comprobante->anticipo;
            if (in_array($anticipo->estado, ['aprobado', 'en_observacion'])) {
                $anticipo->estado = 'pendiente';
                $anticipo->save();
            }
        }

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

        // Verificar si el anticipo asociado está bloqueado
        if ($comprobante->anticipo_id) {
            $anticipo = Anticipo::findOrFail($comprobante->anticipo_id);
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes eliminar un comprobante de un anticipo aprobado o rechazado.');
            }
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
            'mensaje' => 'nullable|string|min:2',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:40960'
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

        // Si un administrador deja una observación, marcar el anticipo como "en_observacion"
        // Esto permite que el usuario pueda modificar los comprobantes para corregir lo indicado
        if (($user->isAdmin() || $user->isAreaAdmin()) && $comprobante->anticipo_id) {
            $anticipo = $comprobante->anticipo;
            // Solo cambiar a "en_observacion" si no está ya aprobado o rechazado
            if (!in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                $anticipo->estado = 'en_observacion';
                $anticipo->save();
            }
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
