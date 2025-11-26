<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BancoController extends Controller
{
    /**
     * Verificar que solo el super admin puede acceder
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Solo el super administrador puede gestionar bancos.');
            }
            return $next($request);
        });
    }

    /**
     * Listar todos los bancos
     */
    public function index()
    {
        $bancos = Banco::orderBy('descripcion')->get();
        return view('bancos.index', compact('bancos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('bancos.create');
    }

    /**
     * Guardar nuevo banco
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:10|unique:bancos,codigo',
            'descripcion' => 'required|string|max:255',
            'activo' => 'boolean',
        ]);

        Banco::create([
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('bancos.index')
                         ->with('success', 'Banco creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $banco = Banco::findOrFail($id);
        return view('bancos.edit', compact('banco'));
    }

    /**
     * Actualizar banco
     */
    public function update(Request $request, $id)
    {
        $banco = Banco::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:10|unique:bancos,codigo,' . $id,
            'descripcion' => 'required|string|max:255',
            'activo' => 'boolean',
        ]);

        $banco->update([
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('bancos.index')
                         ->with('success', 'Banco actualizado correctamente.');
    }

    /**
     * Eliminar banco
     */
    public function destroy($id)
    {
        $banco = Banco::findOrFail($id);
        $banco->delete();

        return redirect()->route('bancos.index')
                         ->with('success', 'Banco eliminado correctamente.');
    }
}

