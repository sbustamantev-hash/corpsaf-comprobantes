<?php

namespace App\Http\Controllers;

use App\Models\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TipoComprobanteController extends Controller
{
    /**
     * Verificar que solo el super admin puede acceder
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Solo el super administrador puede gestionar tipos de comprobante.');
            }
            return $next($request);
        });
    }

    /**
     * Listar todos los tipos de comprobante
     */
    public function index()
    {
        $tiposComprobante = TipoComprobante::orderBy('codigo')->get();
        return view('tipos-comprobante.index', compact('tiposComprobante'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('tipos-comprobante.create');
    }

    /**
     * Guardar nuevo tipo de comprobante
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:tipos_comprobante,codigo',
            'descripcion' => 'required|string|max:255',
            'activo' => 'boolean',
        ]);

        TipoComprobante::create([
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('tipos-comprobante.index')
                         ->with('success', 'Tipo de comprobante creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $tipoComprobante = TipoComprobante::findOrFail($id);
        return view('tipos-comprobante.edit', compact('tipoComprobante'));
    }

    /**
     * Actualizar tipo de comprobante
     */
    public function update(Request $request, $id)
    {
        $tipoComprobante = TipoComprobante::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:10|unique:tipos_comprobante,codigo,' . $id,
            'descripcion' => 'required|string|max:255',
            'activo' => 'boolean',
        ]);

        $tipoComprobante->update([
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('tipos-comprobante.index')
                         ->with('success', 'Tipo de comprobante actualizado correctamente.');
    }

    /**
     * Eliminar tipo de comprobante
     */
    public function destroy($id)
    {
        $tipoComprobante = TipoComprobante::findOrFail($id);
        $tipoComprobante->delete();

        return redirect()->route('tipos-comprobante.index')
                         ->with('success', 'Tipo de comprobante eliminado correctamente.');
    }
}

