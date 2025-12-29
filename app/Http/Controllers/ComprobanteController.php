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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

        $rmv = \App\Models\Configuracion::obtener('rmv', 1130);

        return view('comprobantes.create', compact('anticipo', 'tiposComprobante', 'conceptos', 'rmv'));
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
            'moneda' => 'required|in:soles,dolares,euros',
            'fecha' => 'required|date',
            'detalle' => 'nullable|string',
            'origen' => 'nullable|string|max:255',
            'destino' => 'nullable|string|max:255',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:40960',
            'anticipo_id' => 'nullable|exists:anticipos,id'
        ];

        // Si el concepto es "otros", el campo concepto_otro es obligatorio
        $concepto = \App\Models\Concepto::find($request->concepto);
        if ($concepto && strtoupper($concepto->nombre) === 'OTROS') {
            $rules['concepto_otro'] = 'required|string|max:255';
        }

        // Obtener RMV para validación
        $rmv = \App\Models\Configuracion::obtener('rmv', 1130);

        // Validación específica para Planilla de Movilidad
        $tipoComprobante = \App\Models\TipoComprobante::where('codigo', $request->tipo)->first();
        if ($tipoComprobante && stripos($tipoComprobante->descripcion, 'Planilla de Movilidad') !== false) {
            $rules['moneda'] = 'required|in:soles';
            // 4% del RMV
            $maxMonto = $rmv * 0.04;
            if ($request->monto > $maxMonto) {
                return back()
                    ->withInput()
                    ->withErrors(['monto' => 'El monto máximo para Planilla de Movilidad es del 4% del RMV (S/ ' . number_format($maxMonto, 2) . ').']);
            }
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');

            if (!$file->isValid()) {
                return back()->withInput()->withErrors(['archivo' => 'El archivo subido no es válido (Error: ' . $file->getError() . ')']);
            }

            // Generar nombre único
            $extension = $file->getClientOriginalExtension() ?: 'ext';
            $filename = uniqid('comp_', true) . '.' . $extension;

            // Usar Storage facade directamente para evitar problemas con la abstracción de UploadedFile
            try {
                $archivoPath = Storage::disk('public')->putFileAs('comprobantes', $file, $filename);
            } catch (\Exception $e) {
                Log::error('Error al subir archivo: ' . $e->getMessage());
                return back()->withInput()->withErrors(['archivo' => 'Error interno al guardar el archivo: ' . $e->getMessage()]);
            }

            if (!$archivoPath) {
                return back()->withInput()->withErrors(['archivo' => 'Error al guardar el archivo en el disco.']);
            }
        } else {
            return back()->withInput()->withErrors(['archivo' => 'El archivo es obligatorio y debe ser válido.']);
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

            // Validar que Serie+Número sean únicos dentro del anticipo
            // Esta validación asegura que no se repitan comprobantes dentro de la misma rendición
            $serie = str_pad(strtoupper($validated['serie']), 4, '0', STR_PAD_LEFT);
            $numero = str_pad($validated['numero'], 10, '0', STR_PAD_LEFT);

            $existeComprobante = Comprobante::where('anticipo_id', $anticipo->id)
                ->where('serie', $serie)
                ->where('numero', $numero)
                ->exists();

            if ($existeComprobante) {
                return back()
                    ->withInput()
                    ->withErrors(['serie' => 'Ya existe un comprobante con esta Serie y Número en esta rendición. Los campos Serie y Número deben ser únicos dentro de cada rendición.']);
            }
        } else {
            $serie = str_pad(strtoupper($validated['serie']), 4, '0', STR_PAD_LEFT);
            $numero = str_pad($validated['numero'], 10, '0', STR_PAD_LEFT);
        }

        // VALIDACIÓN GLOBAL: Verificar si ya existe un comprobante con el mismo RUC, Serie y Número
        // Esto evita que cualquier usuario registre un duplicado de un comprobante ya existente
        $duplicadoGlobal = Comprobante::where('ruc_empresa', $validated['ruc_empresa'])
            ->where('serie', $serie)
            ->where('numero', $numero)
            ->exists();

        if ($duplicadoGlobal) {
            return back()
                ->withInput()
                ->withErrors(['numero' => 'Ya existe un comprobante registrado con este RUC, Serie y Número en el sistema.']);
        }

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
            'moneda' => $validated['moneda'],
            'fecha' => $validated['fecha'],
            'detalle' => $validated['detalle'] ?? null,
            'origen' => $validated['origen'] ?? null,
            'destino' => $validated['destino'] ?? null,
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
        $comprobante = Comprobante::with(['user', 'concepto', 'anticipo'])->findOrFail($id);

        // Solo operadores pueden editar
        if (!$user->isOperador()) {
            abort(403, 'Solo los operadores pueden editar comprobantes.');
        }

        // Solo puede editar sus propios comprobantes
        if ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este comprobante.');
        }

        // No permitir edición si el comprobante está aprobado o rechazado
        if (in_array($comprobante->estado, ['aprobado', 'rechazado'])) {
            abort(403, 'No puedes editar un comprobante que ha sido aprobado o rechazado.');
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

        $tiposComprobante = TipoComprobante::where('activo', true)
            ->orderBy('codigo')
            ->get();

        $rmv = \App\Models\Configuracion::obtener('rmv', 1130);

        return view('comprobantes.edit', compact('comprobante', 'conceptos', 'tiposComprobante', 'rmv'));
    }

    // ACTUALIZAR BD
    public function update(Request $request, $id)
    {
        $rules = [
            'tipo' => 'required|exists:tipos_comprobante,codigo',
            'concepto' => 'required|exists:conceptos,id',
            'ruc_empresa' => 'required|string|max:20',
            'serie' => ['required', 'alpha_num', 'max:4'],
            'numero' => ['required', 'regex:/^\d{1,10}$/'],
            'monto' => 'required|numeric',
            'moneda' => 'required|in:soles,dolares,euros',
            'fecha' => 'required|date',
            'detalle' => 'nullable|string',
            'origen' => 'nullable|string|max:255',
            'destino' => 'nullable|string|max:255',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:40960'
        ];

        // Si el concepto es "otros", el campo concepto_otro es obligatorio
        $concepto = \App\Models\Concepto::find($request->concepto);
        if ($concepto && strtoupper($concepto->nombre) === 'OTROS') {
            $rules['concepto_otro'] = 'required|string|max:255';
        }

        // Obtener RMV para validación
        $rmv = \App\Models\Configuracion::obtener('rmv', 1130);

        // Validación específica para Planilla de Movilidad
        $tipoComprobante = \App\Models\TipoComprobante::where('codigo', $request->tipo)->first();
        if ($tipoComprobante && stripos($tipoComprobante->descripcion, 'Planilla de Movilidad') !== false) {
            $rules['moneda'] = 'required|in:soles';
            $rules['origen'] = 'required|string|max:255';
            $rules['destino'] = 'required|string|max:255';
            // 4% del RMV
            $maxMonto = $rmv * 0.04;
            if ($request->monto > $maxMonto) {
                return back()
                    ->withInput()
                    ->withErrors(['monto' => 'El monto máximo para Planilla de Movilidad es del 4% del RMV (S/ ' . number_format($maxMonto, 2) . ').']);
            }
        }

        $validated = $request->validate($rules);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);

        // Si es operador, solo puede actualizar sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para actualizar este comprobante.');
        }

        // No permitir edición si el comprobante está aprobado o rechazado
        if (in_array($comprobante->estado, ['aprobado', 'rechazado'])) {
            abort(403, 'No puedes modificar un comprobante que ha sido aprobado o rechazado.');
        }

        // Preparar serie y número normalizados
        $serie = str_pad(strtoupper($request->serie), 4, '0', STR_PAD_LEFT);
        $numero = str_pad($request->numero, 10, '0', STR_PAD_LEFT);

        // Validar unicidad de Serie+Número dentro del anticipo
        // Si el comprobante tiene un anticipo_id, validar dentro de ese anticipo
        if ($comprobante->anticipo_id) {
            $anticipo = $comprobante->anticipo;
            
            // Verificar si el anticipo está bloqueado
            if (in_array($anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar comprobantes de un anticipo aprobado o rechazado.');
            }

            // Validar que Serie+Número sean únicos dentro del anticipo (excluyendo el comprobante actual)
            $existeComprobante = Comprobante::where('anticipo_id', $anticipo->id)
                ->where('serie', $serie)
                ->where('numero', $numero)
                ->where('id', '!=', $comprobante->id)
                ->exists();

            if ($existeComprobante) {
                return back()
                    ->withInput()
                    ->withErrors(['serie' => 'Ya existe un comprobante con esta Serie y Número en esta rendición. Los campos Serie y Número deben ser únicos dentro de cada rendición.']);
            }
        }

        // VALIDACIÓN GLOBAL: Verificar si ya existe un comprobante con el mismo RUC, Serie y Número (excluyendo el actual)
        $duplicadoGlobal = Comprobante::where('ruc_empresa', $request->ruc_empresa)
            ->where('serie', $serie)
            ->where('numero', $numero)
            ->where('id', '!=', $comprobante->id)
            ->exists();

        if ($duplicadoGlobal) {
            return back()
                ->withInput()
                ->withErrors(['numero' => 'Ya existe un comprobante registrado con este RUC, Serie y Número en el sistema.']);
        }

        if ($request->hasFile('archivo')) {
            // Borrar archivo anterior si existe
            if ($comprobante->archivo) {
                Storage::disk('public')->delete($comprobante->archivo);
            }
            try {
                // Usar putFile para generar nombre automático y guardar en disco público
                $archivoPath = Storage::disk('public')->putFile('comprobantes', $request->file('archivo'));
                $comprobante->archivo = $archivoPath;
            } catch (\Exception $e) {
                Log::error('Error al subir archivo en update: ' . $e->getMessage());
                return back()->withInput()->withErrors(['archivo' => 'Error interno al actualizar el archivo.']);
            }
        }

        // Obtener el concepto para verificar si es "OTROS"
        $conceptoModel = \App\Models\Concepto::findOrFail($request->concepto);

        $comprobante->tipo = $request->tipo;
        $comprobante->concepto_id = $request->concepto;
        $comprobante->concepto_otro = strtoupper($conceptoModel->nombre) === 'OTROS' ? $request->concepto_otro : null;
        $comprobante->serie = $serie;
        $comprobante->numero = $numero;
        $comprobante->ruc_empresa = $request->ruc_empresa;
        $comprobante->monto = $request->monto;
        $comprobante->moneda = $request->moneda;
        $comprobante->fecha = $request->fecha;
        $comprobante->detalle = $request->detalle;
        $comprobante->origen = $validated['origen'] ?? null;
        $comprobante->destino = $validated['destino'] ?? null;

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
        $comprobante = Comprobante::with('anticipo')->findOrFail($id);

        // Super admin puede eliminar cualquier comprobante
        if ($user->isAdmin()) {
            // Permitir eliminación
        }
        // Si es operador, puede eliminar:
        // 1. Sus propios comprobantes
        // 2. Cualquier comprobante de un anticipo del cual es dueño (generador de la rendición)
        elseif (!$user->isAreaAdmin()) {
            $puedeEliminar = false;
            
            // Puede eliminar si es el dueño del comprobante
            if ($comprobante->user_id === $user->id) {
                $puedeEliminar = true;
            }
            // O si es el dueño del anticipo (generador de la rendición)
            elseif ($comprobante->anticipo_id && $comprobante->anticipo) {
                if ($comprobante->anticipo->user_id === $user->id) {
                    $puedeEliminar = true;
                }
            }
            
            if (!$puedeEliminar) {
                abort(403, 'No tienes permisos para eliminar este comprobante.');
            }
        }
        // Area admin: no puede eliminar comprobantes directamente
        else {
            abort(403, 'No tienes permisos para eliminar comprobantes.');
        }

        // No permitir eliminar si el comprobante está aprobado o rechazado
        if (in_array($comprobante->estado, ['aprobado', 'rechazado'])) {
            abort(403, 'No puedes eliminar un comprobante que ha sido aprobado o rechazado.');
        }

        // Verificar si el anticipo asociado está bloqueado
        if ($comprobante->anticipo_id && $comprobante->anticipo) {
            if (in_array($comprobante->anticipo->estado, ['aprobado', 'rechazado'])) {
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
            $archivoPath = $request->file('archivo')->store('observaciones', 'public');
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

    /**
     * Aprobar comprobante individual
     */
    public function aprobar(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo super admin y area admin pueden aprobar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden aprobar comprobantes.');
        }

        $comprobante = Comprobante::with(['user', 'anticipo'])->findOrFail($id);

        // Area admin solo puede aprobar comprobantes de su Empresa
        if ($user->isAreaAdmin() && $comprobante->user->area_id !== $user->area_id) {
            abort(403, 'Solo puedes aprobar comprobantes de tu Empresa.');
        }

        // No permitir aprobar/rechazar comprobantes si el anticipo está aprobado o rechazado
        if ($comprobante->anticipo_id && $comprobante->anticipo) {
            if (in_array($comprobante->anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar el estado de comprobantes de un anticipo aprobado o rechazado.');
            }
        }

        $comprobante->estado = 'aprobado';
        $comprobante->save();

        return redirect()->back()->with('success', 'Comprobante aprobado correctamente.');
    }

    /**
     * Rechazar comprobante individual
     */
    public function rechazar(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Solo super admin y area admin pueden rechazar
        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores pueden rechazar comprobantes.');
        }

        $comprobante = Comprobante::with(['user', 'anticipo'])->findOrFail($id);

        // Area admin solo puede rechazar comprobantes de su Empresa
        if ($user->isAreaAdmin() && $comprobante->user->area_id !== $user->area_id) {
            abort(403, 'Solo puedes rechazar comprobantes de tu Empresa.');
        }

        // No permitir aprobar/rechazar comprobantes si el anticipo está aprobado o rechazado
        if ($comprobante->anticipo_id && $comprobante->anticipo) {
            if (in_array($comprobante->anticipo->estado, ['aprobado', 'rechazado'])) {
                abort(403, 'No puedes modificar el estado de comprobantes de un anticipo aprobado o rechazado.');
            }
        }

        $comprobante->estado = 'rechazado';
        $comprobante->save();

        return redirect()->back()->with('success', 'Comprobante rechazado correctamente.');
    }

    /**
     * Exportar comprobantes aprobados de la empresa a Excel
     */
    public function exportExcel()
    {
        $user = Auth::user();

        // Solo Area Admin puede exportar
        if (!$user->isAreaAdmin()) {
            abort(403, 'Solo los administradores de empresa pueden exportar reportes.');
        }

        // Verificar que el usuario tenga área asignada
        if (!$user->area_id) {
            abort(403, 'No tienes una empresa asignada.');
        }

        // Primero obtener TODOS los comprobantes de la empresa (sin filtrar por estado) para depuración
        $todosComprobantes = Comprobante::with(['user', 'concepto'])
            ->whereHas('user', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            })
            ->get();

        Log::info('Export Excel - Total comprobantes en la empresa: ' . $todosComprobantes->count());
        Log::info('Export Excel - Area ID: ' . $user->area_id);

        // Contar por estado
        $porEstado = $todosComprobantes->groupBy('estado')->map->count();
        Log::info('Export Excel - Comprobantes por estado: ' . json_encode($porEstado->toArray()));

        // Obtener todos los comprobantes aprobados de la empresa
        $comprobantes = Comprobante::with(['user', 'concepto'])
            ->whereHas('user', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            })
            ->where('estado', 'aprobado')
            ->orderBy('fecha', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Log para depuración
        Log::info('Export Excel - Comprobantes aprobados encontrados: ' . $comprobantes->count());

        // Solo exportar comprobantes aprobados (no exportar todos si no hay aprobados)

        // Si no hay comprobantes, generar Excel vacío con solo encabezados
        // (en lugar de hacer redirect)

        // Generar nombre del archivo
        $empresaNombre = $user->area->nombre ?? 'Empresa';
        $filename = 'reporte_comprobantes_' . str_replace(' ', '_', $empresaNombre) . '_' . date('Ymd') . '.xlsx';

        // Generar Excel usando Office Open XML
        try {
            $excelContent = $this->generateExcelFile($comprobantes);

            if (empty($excelContent)) {
                Log::error('Export Excel - El contenido generado está vacío');
                return redirect()->route('comprobantes.index')
                    ->with('error', 'Error al generar el archivo Excel. El archivo está vacío.');
            }
        } catch (\Exception $e) {
            Log::error('Export Excel - Error: ' . $e->getMessage());
            Log::error('Export Excel - Trace: ' . $e->getTraceAsString());
            return redirect()->route('comprobantes.index')
                ->with('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($excelContent),
        ];

        return response($excelContent, 200, $headers);
    }

    /**
     * Generar archivo Excel en formato Office Open XML (.xlsx)
     */
    private function generateExcelFile($comprobantes)
    {
        // Crear directorio temporal
        $tempDir = sys_get_temp_dir() . '/excel_' . uniqid();

        if (!mkdir($tempDir, 0777, true)) {
            Log::error('No se pudo crear el directorio temporal: ' . $tempDir);
            throw new \Exception('No se pudo crear el directorio temporal');
        }

        if (!mkdir($tempDir . '/xl', 0777, true)) {
            Log::error('No se pudo crear el directorio xl');
            throw new \Exception('No se pudo crear el directorio xl');
        }

        if (!mkdir($tempDir . '/xl/worksheets', 0777, true)) {
            Log::error('No se pudo crear el directorio worksheets');
            throw new \Exception('No se pudo crear el directorio worksheets');
        }

        if (!mkdir($tempDir . '/_rels', 0777, true)) {
            Log::error('No se pudo crear el directorio _rels');
            throw new \Exception('No se pudo crear el directorio _rels');
        }

        if (!mkdir($tempDir . '/xl/_rels', 0777, true)) {
            Log::error('No se pudo crear el directorio xl/_rels');
            throw new \Exception('No se pudo crear el directorio xl/_rels');
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>';
        file_put_contents($tempDir . '/[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        file_put_contents($tempDir . '/_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>';
        file_put_contents($tempDir . '/xl/_rels/workbook.xml.rels', $workbookRels);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Comprobantes" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
        file_put_contents($tempDir . '/xl/workbook.xml', $workbook);

        // Calcular dimensiones (mínimo 1 fila para los encabezados)
        $maxRow = max(1, $comprobantes->count() + 1); // +1 para los encabezados
        $maxCol = 7; // 7 columnas

        // xl/worksheets/sheet1.xml
        $sheetContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <dimension ref="A1:' . $this->numberToColumn($maxCol) . $maxRow . '"/>
    <sheetViews>
        <sheetView workbookViewId="0">
            <selection activeCell="A1" sqref="A1"/>
        </sheetView>
    </sheetViews>
    <sheetData>';

        // Encabezados
        $rowNum = 1;
        $headers = ['Nº', 'Fecha', 'Promovedor (RUC)', 'Concepto', 'Monto', 'Serie', 'Número de Comprobante'];
        $sheetContent .= '<row r="' . $rowNum . '">';
        foreach ($headers as $col => $header) {
            $colLetter = $this->numberToColumn($col + 1);
            $sheetContent .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr"><is><t>' . htmlspecialchars($header, ENT_XML1) . '</t></is></c>';
        }
        $sheetContent .= '</row>';

        // Datos
        $numero = 1;
        $totalRows = 0;
        Log::info('Export Excel - Iniciando procesamiento de ' . $comprobantes->count() . ' comprobantes');

        foreach ($comprobantes as $comprobante) {
            $rowNum++;
            $totalRows++;

            try {
                Log::debug('Export Excel - Procesando comprobante ID: ' . $comprobante->id);
                $fecha = $comprobante->fecha instanceof Carbon
                    ? $comprobante->fecha
                    : Carbon::parse($comprobante->fecha);

                $concepto = $comprobante->concepto
                    ? ($comprobante->concepto->descripcion ?? 'N/A')
                    : ($comprobante->concepto_otro ?? 'N/A');

                $rowData = [
                    $numero++, // Nº - número
                    $fecha->format('d/m/Y'), // Fecha - texto
                    $comprobante->ruc_empresa ?? 'N/A', // RUC - texto
                    $concepto, // Concepto - texto
                    (float) $comprobante->monto, // Monto - número (asegurar que sea float)
                    $comprobante->serie ?? 'N/A', // Serie - texto
                    $comprobante->numero ?? 'N/A' // Número - texto
                ];

                $sheetContent .= '<row r="' . $rowNum . '">';
                foreach ($rowData as $col => $value) {
                    $colLetter = $this->numberToColumn($col + 1);
                    // Columna 0 (Nº) y columna 4 (Monto) son numéricas
                    if (($col == 0 || $col == 4) && is_numeric($value)) {
                        $sheetContent .= '<c r="' . $colLetter . $rowNum . '" t="n"><v>' . htmlspecialchars((string) $value, ENT_XML1) . '</v></c>';
                    } else {
                        $sheetContent .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr"><is><t>' . htmlspecialchars((string) $value, ENT_XML1) . '</t></is></c>';
                    }
                }
                $sheetContent .= '</row>';
            } catch (\Exception $e) {
                // Continuar con el siguiente comprobante si hay error
                \Log::warning('Error procesando comprobante ID ' . $comprobante->id . ': ' . $e->getMessage());
                continue;
            }
        }

        // Log final
        Log::info('Export Excel - Total filas procesadas: ' . $totalRows);

        // Si no se procesó ningún comprobante, al menos tener los encabezados
        if ($totalRows == 0) {
            Log::warning('Export Excel - No se procesaron comprobantes en la exportación');
        }

        $sheetContent .= '</sheetData>
</worksheet>';

        // Verificar que se escribió correctamente
        $written = file_put_contents($tempDir . '/xl/worksheets/sheet1.xml', $sheetContent);
        if ($written === false) {
            $this->deleteDirectory($tempDir);
            abort(500, 'Error al escribir el archivo de la hoja de cálculo.');
        }

        // Verificar que todos los archivos necesarios existen
        $requiredFiles = [
            '[Content_Types].xml',
            '_rels/.rels',
            'xl/workbook.xml',
            'xl/_rels/workbook.xml.rels',
            'xl/worksheets/sheet1.xml'
        ];

        foreach ($requiredFiles as $file) {
            if (!file_exists($tempDir . '/' . $file)) {
                $this->deleteDirectory($tempDir);
                abort(500, 'Archivo requerido no encontrado: ' . $file);
            }
        }

        // Crear archivo ZIP usando comando del sistema
        $zipFile = sys_get_temp_dir() . '/excel_' . uniqid() . '.zip';
        $oldCwd = getcwd();

        try {
            chdir($tempDir);

            // Usar ruta absoluta para el zip
            $zipFileAbsolute = realpath(sys_get_temp_dir()) . '/' . basename($zipFile);

            // Verificar que el comando zip existe
            exec("which zip 2>&1", $whichOutput, $whichCode);
            if ($whichCode !== 0) {
                throw new \Exception('El comando zip no está disponible en el sistema');
            }

            // Crear el ZIP con todos los archivos
            exec("zip -r " . escapeshellarg($zipFileAbsolute) . " . -q 2>&1", $output, $returnCode);

            chdir($oldCwd);

            if ($returnCode !== 0) {
                // Limpiar y retornar error con detalles
                $errorMsg = implode("\n", $output);
                $this->deleteDirectory($tempDir);
                @unlink($zipFileAbsolute);
                Log::error('Error al generar Excel: ' . $errorMsg);
                throw new \Exception('Error al generar archivo Excel: ' . $errorMsg);
            }
        } catch (\Exception $e) {
            chdir($oldCwd);
            $this->deleteDirectory($tempDir);
            @unlink($zipFileAbsolute ?? '');
            throw $e;
        }

        // Verificar que el archivo se creó
        if (!file_exists($zipFileAbsolute)) {
            $this->deleteDirectory($tempDir);
            abort(500, 'No se pudo crear el archivo Excel.');
        }

        // Leer contenido del ZIP
        $content = file_get_contents($zipFileAbsolute);

        if (empty($content)) {
            $this->deleteDirectory($tempDir);
            @unlink($zipFileAbsolute);
            abort(500, 'El archivo Excel generado está vacío.');
        }

        // Limpiar archivos temporales
        $this->deleteDirectory($tempDir);
        @unlink($zipFileAbsolute);

        return $content;
    }

    /**
     * Convertir número de columna a letra (1 = A, 2 = B, etc.)
     */
    private function numberToColumn($number)
    {
        $column = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder) . $column;
            $number = intval(($number - $remainder) / 26);
        }
        return $column;
    }

    /**
     * Eliminar directorio recursivamente
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
}
