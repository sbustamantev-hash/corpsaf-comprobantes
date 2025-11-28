<?php

namespace App\Http\Controllers;

use App\Models\Concepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConceptoController extends Controller
{
    /**
     * Mostrar lista de conceptos
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede acceder a esta sección.');
        }

        $conceptos = Concepto::orderBy('nombre')->get();

        return view('conceptos.index', compact('conceptos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede crear conceptos.');
        }

        return view('conceptos.create');
    }

    /**
     * Guardar nuevo concepto
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede crear conceptos.');
        }

        $request->validate([
            'nombre' => 'required|string|max:100|unique:conceptos,nombre',
            'activo' => 'boolean',
        ]);

        Concepto::create([
            'nombre' => strtoupper(trim($request->nombre)),
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('conceptos.index')
            ->with('success', 'Concepto creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede editar conceptos.');
        }

        $concepto = Concepto::findOrFail($id);

        return view('conceptos.edit', compact('concepto'));
    }

    /**
     * Actualizar concepto
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede editar conceptos.');
        }

        $concepto = Concepto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:conceptos,nombre,' . $concepto->id,
            'activo' => 'boolean',
        ]);

        $concepto->update([
            'nombre' => strtoupper(trim($request->nombre)),
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('conceptos.index')
            ->with('success', 'Concepto actualizado correctamente.');
    }

    /**
     * Eliminar concepto
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede eliminar conceptos.');
        }

        $concepto = Concepto::findOrFail($id);
        $concepto->delete();

        return redirect()->route('conceptos.index')
            ->with('success', 'Concepto eliminado correctamente.');
    }
}

