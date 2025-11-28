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
        /** @var \App\Models\User $user */
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede modificar las configuraciones.');
        }

        $request->validate([
            'nombre_app' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:40960',
            'nombre_empresa' => 'nullable|string|max:255'
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

        Configuracion::establecer('nombre_empresa', $request->nombre_empresa ?? $request->nombre_app, 'text');

        return redirect()->route('configuraciones.index')
            ->with('success', 'Configuraciones actualizadas correctamente.');
    }

    /**
     * Actualizar únicamente el logo desde el sidebar
     */
    public function updateBranding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403, 'Solo el super administrador puede modificar el logo.');
        }

        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:40960',
            'nombre_empresa' => 'required|string|max:255'
        ]);

        if ($request->hasFile('logo')) {
            $logoAnterior = Configuracion::obtener('logo_path');
            if ($logoAnterior && Storage::disk('public')->exists($logoAnterior)) {
                Storage::disk('public')->delete($logoAnterior);
            }

            $logoPath = $request->file('logo')->store('logos', ['disk' => 'public']);
            Configuracion::establecer('logo_path', $logoPath, 'image');
        }

        Configuracion::establecer('nombre_empresa', $request->nombre_empresa, 'text');
        Configuracion::establecer('nombre_app', $request->nombre_empresa, 'text');

        return redirect()->back()->with('success', 'Identidad actualizada correctamente.');
    }
}
