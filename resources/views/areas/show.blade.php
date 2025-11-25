@extends('layouts.app')

@section('title', 'Detalles del Área')
@section('subtitle', 'Información del área/empresa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Información del área -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ $area->nombre }}</h2>
            <div class="flex items-center space-x-2">
                @if($area->activo)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Activa
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-times-circle mr-1"></i>Inactiva
                    </span>
                @endif
                <a href="{{ route('areas.edit', $area->id) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Código</label>
                <p class="text-gray-900">{{ $area->codigo ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Total Usuarios</label>
                <p class="text-gray-900">{{ $area->users->count() }}</p>
            </div>
            @if($area->descripcion)
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-500 mb-1">Descripción</label>
                <p class="text-gray-900">{{ $area->descripcion }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- Usuarios del área -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Usuarios del Área</h3>
            <button onclick="toggleUserForm()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                <i class="fas fa-user-plus mr-2"></i>Agregar Usuario
            </button>
        </div>

        <!-- Formulario para agregar usuario (oculto por defecto) -->
        <div id="user-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Nuevo Usuario</h4>
            <form action="{{ route('areas.users.store', $area->id) }}" method="POST">
                @csrf
                <input type="hidden" name="form_type" value="create_user">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                        <input type="text" 
                               name="name" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                               value="{{ old('name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">DNI *</label>
                        <input type="text" 
                               name="dni" 
                               required
                               maxlength="20"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dni') border-red-500 @enderror" 
                               value="{{ old('dni') }}"
                               placeholder="Ej: 12345678">
                        @error('dni')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">El DNI será usado para iniciar sesión</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email (opcional)</label>
                        <input type="email" 
                               name="email" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                               value="{{ old('email') }}">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono (opcional)</label>
                        <input type="text" 
                               name="telefono" 
                               maxlength="20"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror" 
                               value="{{ old('telefono') }}"
                               placeholder="Ej: 987654321">
                        @error('telefono')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña *</label>
                        <input type="password" 
                               name="password" 
                               required
                               minlength="8"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rol *</label>
                        <select name="role" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror">
                            <option value="">Seleccione un rol</option>
                            <option value="area_admin" {{ old('role') == 'area_admin' ? 'selected' : '' }}>Administrador de Área</option>
                            <option value="operador" {{ old('role') == 'operador' ? 'selected' : '' }}>Usuario / Trabajador</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end space-x-3">
                    <button type="button" 
                            onclick="toggleUserForm()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>Crear Usuario
                    </button>
                </div>
            </form>
        </div>
        
        @if($area->users->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($area->users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->dni ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->telefono ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($user->isAdmin()) bg-red-100 text-red-800
                                        @elseif($user->isAreaAdmin()) bg-purple-100 text-purple-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ $user->role_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(!$user->isAdmin())
                                    <div class="flex items-center space-x-2">
                                        <button onclick="toggleEditForm({{ $user->id }})" 
                                                class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded transition" 
                                                title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('areas.users.destroy', [$area->id, $user->id]) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded transition" 
                                                    title="Eliminar usuario">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No hay usuarios asignados a esta área</p>
        @endif

        <!-- Formularios de edición para cada usuario (ocultos por defecto) -->
        @foreach($area->users as $user)
            @if(!$user->isAdmin())
            <div id="edit-form-{{ $user->id }}" class="hidden mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Editar Usuario: {{ $user->name }}</h4>
                <form action="{{ route('areas.users.update', [$area->id, $user->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="form_type" value="edit_user">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                            <input type="text" 
                                   name="name" 
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                                   value="{{ old('name', $user->name) }}">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">DNI *</label>
                            <input type="text" 
                                   name="dni" 
                                   required
                                   maxlength="20"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dni') border-red-500 @enderror" 
                                   value="{{ old('dni', $user->dni) }}">
                            @error('dni')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email (opcional)</label>
                            <input type="email" 
                                   name="email" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                                   value="{{ old('email', $user->email) }}">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono (opcional)</label>
                            <input type="text" 
                                   name="telefono" 
                                   maxlength="20"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('telefono') border-red-500 @enderror" 
                                   value="{{ old('telefono', $user->telefono) }}">
                            @error('telefono')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña (opcional)</label>
                            <input type="password" 
                                   name="password" 
                                   minlength="8"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                                   placeholder="Dejar vacío para mantener la actual">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Dejar vacío si no deseas cambiar la contraseña</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rol *</label>
                            <select name="role" 
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror">
                                <option value="">Seleccione un rol</option>
                                <option value="area_admin" {{ old('role', $user->role) == 'area_admin' ? 'selected' : '' }}>Administrador de Área</option>
                                <option value="operador" {{ old('role', $user->role) == 'operador' ? 'selected' : '' }}>Usuario / Trabajador</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end space-x-3">
                        <button type="button" 
                                onclick="toggleEditForm({{ $user->id }})"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-save mr-2"></i>Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
            @endif

        @endforeach

    </div>

    <!-- Botón volver -->
    <div class="flex justify-end">
        <a href="{{ route('areas.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Áreas
        </a>
    </div>
</div>

<script>
    function hideAllForms() {
        document.getElementById('user-form').classList.add('hidden');
        document.querySelectorAll('[id^="edit-form-"]').forEach(form => form.classList.add('hidden'));
    }

    function toggleUserForm() {
        const form = document.getElementById('user-form');
        const isHidden = form.classList.contains('hidden');
        hideAllForms();
        if (isHidden) {
            form.classList.remove('hidden');
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function toggleEditForm(userId) {
        const editForm = document.getElementById('edit-form-' + userId);
        const isHidden = editForm.classList.contains('hidden');
        hideAllForms();
        if (isHidden) {
            editForm.classList.remove('hidden');
            editForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // Mostrar formulario si hay errores de validación
    @if($errors->any())
        @php
            $formType = old('form_type');
            $editUserId = old('user_id');
        @endphp
        @if($formType === 'edit_user' && $editUserId)
            document.getElementById('edit-form-{{ $editUserId }}')?.classList.remove('hidden');
        @else
            document.getElementById('user-form')?.classList.remove('hidden');
        @endif
    @endif
</script>
@endsection

