<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComprobanteController extends Controller
{
    // LISTAR COMPROBANTES
    public function index()
    {
        $comprobantes = Comprobante::orderBy('id', 'desc')->get();
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

        // Asignar automáticamente al primer usuario (admin)
        $user = User::first();

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
        $comprobante = Comprobante::findOrFail($id);
        return view('comprobantes.show', compact('comprobante'));
    }

    // FORMULARIO EDITAR
    public function edit($id)
    {
        $comprobante = Comprobante::findOrFail($id);
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

        $comprobante = Comprobante::findOrFail($id);

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
        $comprobante = Comprobante::findOrFail($id);

        // Borrar archivo si existe
        if ($comprobante->archivo) {
            Storage::disk('public')->delete($comprobante->archivo);
        }

        $comprobante->delete();

        return redirect()->route('comprobantes.index')
                         ->with('success', 'Comprobante eliminado correctamente.');
    }
}
