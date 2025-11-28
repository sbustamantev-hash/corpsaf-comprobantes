<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Listar usuarios
     */
    public function index()
    {
        $user = Auth::user();

        // Super admin: ver todos los usuarios
        if ($user->isAdmin()) {
            $users = User::with('area')
                ->orderBy('name')
                ->get();
        }
        // Area admin: ver solo usuarios de su Empresa
        elseif ($user->isAreaAdmin()) {
            $users = User::with('area')
                ->where('area_id', $user->area_id)
                ->where('id', '!=', $user->id) // No mostrar al mismo admin
                ->orderBy('name')
                ->get();
        }
        // Operador: no puede ver usuarios
        else {
            abort(403, 'No tienes permisos para ver usuarios.');
        }

        $areas = Area::orderBy('nombre')->get();

        return view('users.index', compact('users', 'areas'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'No tienes permisos para crear usuarios.');
        }

        $areas = Area::orderBy('nombre')->get();

        return view('users.create', compact('areas'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isAreaAdmin()) {
            abort(403, 'No tienes permisos para crear usuarios.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, [Role::AREA_ADMIN, Role::OPERADOR])) {
                        $fail('El rol debe ser Administrador de Empresa o Usuario/Trabajador.');
                    }
                }
            ],
        ];

        // Si es area admin, solo puede asignar a su Empresa
        if ($user->isAreaAdmin()) {
            $rules['area_id'] = 'required|exists:areas,id';
        } else {
            $rules['area_id'] = 'nullable|exists:areas,id';
        }

        $validated = $request->validate($rules);

        // Si es area admin, forzar su Empresa
        if ($user->isAreaAdmin()) {
            $validated['area_id'] = $user->area_id;
        }

        User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?: null,
            'telefono' => $validated['telefono'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'area_id' => $validated['area_id'] ?? null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $currentUser = Auth::user();
        $user = User::with('area')->findOrFail($id);

        // Verificar permisos
        if ($currentUser->isAreaAdmin()) {
            if ($user->area_id !== $currentUser->area_id || $user->isAdmin()) {
                abort(403, 'No tienes permisos para editar este usuario.');
            }
        } elseif (!$currentUser->isAdmin()) {
            abort(403, 'No tienes permisos para editar usuarios.');
        }

        // No permitir editar super admin
        if ($user->isAdmin() && !$currentUser->isAdmin()) {
            abort(403, 'No se puede editar un super administrador.');
        }

        $areas = Area::orderBy('nombre')->get();

        return view('users.edit', compact('user', 'areas'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Verificar permisos
        if ($currentUser->isAreaAdmin()) {
            if ($user->area_id !== $currentUser->area_id || $user->isAdmin()) {
                abort(403, 'No tienes permisos para editar este usuario.');
            }
        } elseif (!$currentUser->isAdmin()) {
            abort(403, 'No tienes permisos para editar usuarios.');
        }

        // No permitir editar super admin
        if ($user->isAdmin() && !$currentUser->isAdmin()) {
            abort(403, 'No se puede editar un super administrador.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, [Role::AREA_ADMIN, Role::OPERADOR])) {
                        $fail('El rol debe ser Administrador de Empresa o Usuario/Trabajador.');
                    }
                }
            ],
        ];

        // Si es area admin, solo puede asignar a su Empresa
        if ($currentUser->isAreaAdmin()) {
            $rules['area_id'] = 'required|exists:areas,id';
        } else {
            $rules['area_id'] = 'nullable|exists:areas,id';
        }

        $validated = $request->validate($rules);

        // Si es area admin, forzar su Empresa
        if ($currentUser->isAreaAdmin()) {
            $validated['area_id'] = $currentUser->area_id;
        }

        $userData = [
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?: null,
            'telefono' => $validated['telefono'] ?? null,
            'role' => $validated['role'],
            'area_id' => $validated['area_id'] ?? null,
        ];

        // Solo actualizar contraseña si se proporciona
        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Verificar permisos
        if ($currentUser->isAreaAdmin()) {
            if ($user->area_id !== $currentUser->area_id || $user->isAdmin()) {
                abort(403, 'No tienes permisos para eliminar este usuario.');
            }
        } elseif (!$currentUser->isAdmin()) {
            abort(403, 'No tienes permisos para eliminar usuarios.');
        }

        // No permitir eliminar super admin
        if ($user->isAdmin()) {
            abort(403, 'No se puede eliminar un super administrador.');
        }

        // No permitir auto-eliminación
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}

