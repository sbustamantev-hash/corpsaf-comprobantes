<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    /**
     * Mostrar página de configuraciones
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede acceder a las configuraciones.');
        }

        $nombreApp = Configuracion::obtener('nombre_app', 'YnnovaCorp');
        $logoPath = Configuracion::obtener('logo_path', null);

        return view('configuraciones.index', compact('nombreApp', 'logoPath'));
    }

    /**
     * Actualizar configuraciones
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede modificar las configuraciones.');
        }

        $request->validate([
            'nombre_app' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Actualizar nombre de la aplicación
        Configuracion::establecer('nombre_app', $request->nombre_app, 'text');

        // Manejar logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            $logoAnterior = Configuracion::obtener('logo_path');
            if ($logoAnterior && Storage::disk('public')->exists($logoAnterior)) {
                Storage::disk('public')->delete($logoAnterior);
            }

            // Guardar nuevo logo
            $logoPath = $request->file('logo')->store('logos', ['disk' => 'public']);
            Configuracion::establecer('logo_path', $logoPath, 'image');
        }

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuraciones actualizadas correctamente.');
    }
}
