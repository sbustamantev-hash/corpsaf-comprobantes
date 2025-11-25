<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AreaController extends Controller
{
    /**
     * Verificar que solo el super admin puede acceder
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                abort(403, 'Solo el super administrador puede gestionar áreas.');
            }
            return $next($request);
        });
    }

    /**
     * Listar todas las áreas
     */
    public function index()
    {
        $areas = Area::withCount(['users', 'comprobantes'])->orderBy('nombre')->get();
        return view('areas.index', compact('areas'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('areas.create');
    }

    /**
     * Guardar nueva área
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre',
            'codigo' => 'nullable|string|max:50|unique:areas,codigo',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Area::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('areas.index')
                         ->with('success', 'Área creada correctamente.');
    }

    /**
     * Mostrar detalles de un área
     */
    public function show($id)
    {
        $area = Area::with([
                    'users.anticipos',
                    'anticipos.usuario',
                    'anticipos.banco',
                    'anticipos.comprobantes',
                ])->findOrFail($id);

        return view('areas.show', compact('area'));
    }

    /**
     * Crear usuario para el área
     */
    public function storeUser(Request $request, $area)
    {
        $area = Area::findOrFail($area);

        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!in_array($value, [Role::AREA_ADMIN, Role::OPERADOR])) {
                    $fail('El rol debe ser Administrador de Área o Usuario/Trabajador.');
                }
            }],
        ]);

        User::create([
            'name' => $request->name,
            'dni' => $request->dni,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'area_id' => $area->id,
        ]);

        return redirect()->route('areas.show', $area->id)
                         ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $area = Area::findOrFail($id);
        return view('areas.edit', compact('area'));
    }

    /**
     * Actualizar área
     */
    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:areas,nombre,' . $area->id,
            'codigo' => 'nullable|string|max:50|unique:areas,codigo,' . $area->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $area->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo') ? true : false,
        ]);

        return redirect()->route('areas.index')
                         ->with('success', 'Área actualizada correctamente.');
    }

    /**
     * Eliminar área
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);

        // Verificar si tiene usuarios asociados
        if ($area->users()->count() > 0) {
            return redirect()->route('areas.index')
                           ->with('error', 'No se puede eliminar el área porque tiene usuarios asociados.');
        }

        $area->delete();

        return redirect()->route('areas.index')
                         ->with('success', 'Área eliminada correctamente.');
    }

    /**
     * Actualizar usuario del área
     */
    public function updateUser(Request $request, $area, $user)
    {
        $area = Area::findOrFail($area);
        $user = User::findOrFail($user);

        // Verificar que el usuario pertenece al área
        if ($user->area_id !== $area->id) {
            return redirect()->route('areas.show', $area->id)
                           ->with('error', 'El usuario no pertenece a esta área.');
        }

        // No permitir editar super admin
        if ($user->isAdmin()) {
            return redirect()->route('areas.show', $area->id)
                           ->with('error', 'No se puede editar un super administrador.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!in_array($value, [Role::AREA_ADMIN, Role::OPERADOR])) {
                    $fail('El rol debe ser Administrador de Área o Usuario/Trabajador.');
                }
            }],
        ]);

        $userData = [
            'name' => $request->name,
            'dni' => $request->dni,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'role' => $request->role,
        ];

        // Solo actualizar contraseña si se proporciona
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('areas.show', $area->id)
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario del área
     */
    public function destroyUser($area, $user)
    {
        $area = Area::findOrFail($area);
        $user = User::findOrFail($user);

        // Verificar que el usuario pertenece al área
        if ($user->area_id !== $area->id) {
            return redirect()->route('areas.show', $area->id)
                           ->with('error', 'El usuario no pertenece a esta área.');
        }

        // No permitir eliminar super admin
        if ($user->isAdmin()) {
            return redirect()->route('areas.show', $area->id)
                           ->with('error', 'No se puede eliminar un super administrador.');
        }

        $user->delete();

        return redirect()->route('areas.show', $area->id)
                         ->with('success', 'Usuario eliminado correctamente.');
    }
}
