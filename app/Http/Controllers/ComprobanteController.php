<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\User;
use App\Models\Observacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ComprobanteController extends Controller
{
    // LISTAR COMPROBANTES
    public function index()
    {
        $user = Auth::user();
        
        // Si es admin, ver todos los comprobantes
        // Si es operador/trabajador, solo los suyos
        if ($user->isAdmin()) {
            $comprobantes = Comprobante::orderBy('id', 'desc')->get();
        } else {
            $comprobantes = Comprobante::where('user_id', $user->id)
                                      ->orderBy('id', 'desc')
                                      ->get();
        }
        
        return view('comprobantes.index', compact('comprobantes'));
    }

    // FORMULARIO CREAR
    public function create()
    {
        return view('comprobantes.create');
    }

    // GUARDAR EN BD
    public function store(Request $request)
    {
        $request->validate([
            'tipo'    => 'required|string|max:50',
            'monto'   => 'required|numeric',
            'fecha'   => 'required|date',
            'detalle' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $archivoPath = null;

        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('comprobantes', ['disk' => 'public']);
        }

        // Asignar al usuario autenticado
        $user = Auth::user();

        Comprobante::create([
            'user_id' => $user->id,
            'tipo'    => $request->tipo,
            'monto'   => $request->monto,
            'fecha'   => $request->fecha,
            'detalle' => $request->detalle,
            'archivo' => $archivoPath,
            'estado'  => 'pendiente'
        ]);

        return redirect()->route('comprobantes.index')
                         ->with('success', 'Comprobante registrado correctamente.');
    }

    // VER DETALLES
    public function show($id)
    {
        $user = Auth::user();
        $comprobante = Comprobante::with(['user', 'observaciones.user'])->findOrFail($id);
        
        // Si es operador, solo puede ver sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este comprobante.');
        }
        
        return view('comprobantes.show', compact('comprobante'));
    }

    // FORMULARIO EDITAR
    public function edit($id)
    {
        $user = Auth::user();
        $comprobante = Comprobante::with('user')->findOrFail($id);
        
        // Admin no puede editar
        if ($user->isAdmin()) {
            abort(403, 'El administrador no puede editar comprobantes. Use la vista de detalles para aprobar o rechazar.');
        }
        
        // Si es operador, solo puede editar sus propios comprobantes
        if ($comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para editar este comprobante.');
        }
        
        return view('comprobantes.edit', compact('comprobante'));
    }

    // ACTUALIZAR BD
    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo'    => 'required|string|max:50',
            'monto'   => 'required|numeric',
            'fecha'   => 'required|date',
            'detalle' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);
        
        // Si es operador, solo puede actualizar sus propios comprobantes
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para actualizar este comprobante.');
        }

        if ($request->hasFile('archivo')) {
            // Borrar archivo anterior si existe
            if ($comprobante->archivo) {
                Storage::disk('public')->delete($comprobante->archivo);
            }
            $archivoPath = $request->file('archivo')->store('comprobantes', ['disk' => 'public']);
            $comprobante->archivo = $archivoPath;
        }

        $comprobante->tipo    = $request->tipo;
        $comprobante->monto   = $request->monto;
        $comprobante->fecha   = $request->fecha;
        $comprobante->detalle = $request->detalle;

        $comprobante->save();

        return redirect()->route('comprobantes.index')
                         ->with('success', 'Comprobante actualizado correctamente.');
    }

    // ELIMINAR
    public function destroy($id)
    {
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
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);
        
        // Verificar permisos: admin puede ver todos, operador solo los suyos
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
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

    // APROBAR COMPROBANTE (solo admin)
    public function aprobar(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Solo los administradores pueden aprobar comprobantes.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:10',
        ]);

        $comprobante = Comprobante::findOrFail($id);
        
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

    // RECHAZAR COMPROBANTE (solo admin)
    public function rechazar(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Solo los administradores pueden rechazar comprobantes.');
        }

        $request->validate([
            'mensaje' => 'required|string|min:10',
        ]);

        $comprobante = Comprobante::findOrFail($id);
        
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

    // AGREGAR OBSERVACIÓN (cualquier usuario autenticado)
    public function agregarObservacion(Request $request, $id)
    {
        $user = Auth::user();
        $comprobante = Comprobante::findOrFail($id);
        
        // Verificar que el usuario tenga acceso al comprobante
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
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

        return redirect()->route('comprobantes.show', $comprobante->id)
                         ->with('success', 'Observación agregada correctamente.');
    }

    // DESCARGAR ARCHIVO DE OBSERVACIÓN
    public function downloadObservacion($id)
    {
        $observacion = Observacion::findOrFail($id);
        $user = Auth::user();
        
        // Verificar que el usuario tenga acceso al comprobante
        $comprobante = $observacion->comprobante;
        if (!$user->isAdmin() && $comprobante->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este archivo.');
        }
        
        if (!$observacion->archivo || !Storage::disk('public')->exists($observacion->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }
        
        return Storage::disk('public')->download($observacion->archivo);
    }
}
