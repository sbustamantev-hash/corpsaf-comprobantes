<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('sistemas.index');
        }
        
        $nombreApp = Configuracion::obtener('nombre_app', 'YnnovaCorp');
        $logoPath = Configuracion::obtener('logo_path', null);
        
        return view('auth.login', compact('nombreApp', 'logoPath'));
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $request->validate([
            'dni' => 'required|string',
            'password' => 'required|string',
        ]);

        $dni = $request->input('dni');
        $password = $request->input('password');
        $remember = $request->filled('remember');

        // Buscar usuario por DNI
        $user = \App\Models\User::where('dni', $dni)->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            return redirect()->intended(route('sistemas.index'));
        }

        throw ValidationException::withMessages([
            'dni' => ['Las credenciales proporcionadas no son correctas.'],
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

