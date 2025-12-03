<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Configuracion;

class SistemaController extends Controller
{
    /**
     * Mostrar pantalla de selección de sistemas
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $logoPath = Configuracion::obtener('logo_path', null);
        $user = Auth::user();

        // Sistema de gestión - disponible para todos
        $sistemas = [
            [
                'id' => 'comprobantes',
                'nombre' => 'Sistema de gestión',
                'subtitulo' => 'Entrega a rendir',
                'descripcion' => 'Gestiona anticipos, reembolsos y comprobantes',
                'icono' => 'fa-file-invoice-dollar',
                'color' => 'blue',
                'ruta' => route('comprobantes.index')
            ]
        ];

        // Nuevo sistema - solo para administradores (super admin y area admin)
        // Nuevo sistema - visible para todos, pero restringido al ingresar
        $sistemas[] = [
            'id' => 'marketing',
            'nombre' => 'Nuevo sistema',
            'subtitulo' => '',
            'descripcion' => '',
            'icono' => 'fa-question',
            'color' => 'gray',
            'ruta' => route('requerimientos.index')
        ];

        return view('sistemas.index', compact('sistemas', 'logoPath'));
    }

    /**
     * Seleccionar un sistema y redirigir
     */
    public function seleccionar(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $sistemaId = $request->input('sistema_id');

        // Por ahora solo hay un sistema
        if ($sistemaId === 'comprobantes') {
            return redirect()->route('comprobantes.index');
        }

        if ($sistemaId === 'marketing') {
            $user = Auth::user();
            if (!$user->isAdmin() && !$user->isAreaAdmin() && !$user->isMarketingAdmin()) {
                return redirect()->route('sistemas.index')
                    ->with('error', 'No tienes permisos para acceder a este sistema.');
            }
            return redirect()->route('requerimientos.index');
        }

        return redirect()->route('sistemas.index')
            ->with('error', 'Estamos programando para ti');
    }
}

