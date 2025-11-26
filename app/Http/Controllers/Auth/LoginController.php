<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        return view('auth.login');
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

