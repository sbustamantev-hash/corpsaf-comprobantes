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
        $selectedRole = $request->input('role'); // Rol seleccionado si hay múltiples usuarios

        // Buscar todos los usuarios con este DNI
        $users = \App\Models\User::where('dni', $dni)->get();

        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'dni' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        // Verificar contraseña con el primer usuario (todos deberían tener la misma contraseña)
        $firstUser = $users->first();
        if (!\Illuminate\Support\Facades\Hash::check($password, $firstUser->password)) {
            throw ValidationException::withMessages([
                'dni' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        // Si hay múltiples usuarios y no se seleccionó un rol, mostrar pantalla de selección
        if ($users->count() > 1 && !$selectedRole) {
            // Guardar datos en sesión para la selección de rol
            $request->session()->put('login_dni', $dni);
            $request->session()->put('login_password', $password);
            $request->session()->put('login_remember', $remember);
            $request->session()->put('login_users', $users->toArray());
            
            return redirect()->route('login.select-role');
        }

        // Si se seleccionó un rol, buscar ese usuario específico
        if ($selectedRole) {
            $user = $users->where('role', $selectedRole)->first();
            if (!$user) {
                // Limpiar sesión de login
                $request->session()->forget(['login_dni', 'login_password', 'login_remember', 'login_users']);
                throw ValidationException::withMessages([
                    'dni' => ['El rol seleccionado no es válido.'],
                ]);
            }
            // Limpiar sesión de login después de autenticar
            $request->session()->forget(['login_dni', 'login_password', 'login_remember', 'login_users']);
        } else {
            // Si solo hay un usuario, usar ese
            $user = $firstUser;
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended(route('sistemas.index'));
    }

    /**
     * Mostrar formulario de selección de rol
     */
    public function showRoleSelection(Request $request)
    {
        $dni = $request->session()->get('login_dni');
        $password = $request->session()->get('login_password');
        $remember = $request->session()->get('login_remember');
        $usersData = $request->session()->get('login_users');

        if (!$dni || !$usersData) {
            return redirect()->route('login');
        }

        // Convertir los datos de sesión de vuelta a modelos User
        $users = collect($usersData)->map(function ($userData) {
            return \App\Models\User::find($userData['id']);
        })->filter();

        if ($users->isEmpty()) {
            return redirect()->route('login');
        }

        $nombreApp = Configuracion::obtener('nombre_app', 'YnnovaCorp');
        $logoPath = Configuracion::obtener('logo_path', null);

        return view('auth.select-role', compact('dni', 'password', 'remember', 'users', 'nombreApp', 'logoPath'));
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

