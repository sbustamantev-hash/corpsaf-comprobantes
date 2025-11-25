@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Resumen de tus comprobantes')

@section('header-actions')
    <a href="{{ route('comprobantes.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
        <i class="fas fa-plus mr-2"></i>
        Nuevo Comprobante
    </a>
@endsection

@section('content')
    @php
        $user = Auth::user();
        if ($user->isAdmin()) {
            $pendientes = $comprobantes->where('estado', 'pendiente')->count();
            $aprobados = $comprobantes->where('estado', 'aprobado')->count();
            $rechazados = $comprobantes->where('estado', 'rechazado')->count();
        } else {
            $pendientes = $comprobantes->where('estado', 'pendiente')->count();
            $aprobados = $comprobantes->where('estado', 'aprobado')->count();
            $rechazados = $comprobantes->where('estado', 'rechazado')->count();
        }
        $total = $comprobantes->count();
    @endphp

    @php
        $anticipos = isset($anticipos) ? $anticipos : collect();
        $tiposComprobante = isset($tiposComprobante) ? $tiposComprobante : collect();
    @endphp

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $pendientes }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Aprobados</p>
                    <p class="text-3xl font-bold text-green-600">{{ $aprobados }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Rechazados</p>
                    <p class="text-3xl font-bold text-red-600">{{ $rechazados }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $total }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->isAreaAdmin())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Usuarios de mi Área</h3>
                <p class="text-sm text-gray-500">Crea anticipos o reembolsos para tus colaboradores</p>
            </div>
        </div>
        @if($operadores->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($operadores as $operador)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $operador->name }}
                                    <p class="text-xs text-gray-500">Usuario / Trabajador</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $operador->dni ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $operador->email ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('areas.users.anticipos.create', [Auth::user()->area_id, $operador->id]) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Crear anticipo
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-gray-500 text-center">
                No hay usuarios cargados en tu área todavía.
            </div>
        @endif
    </div>
    @endif

    @if(Auth::user()->isOperador())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Anticipos / Reembolsos Asignados</h3>
                <p class="text-sm text-gray-500">Registra tus comprobantes de cada anticipo</p>
            </div>
        </div>
        @if($anticipos->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($anticipos as $anticipo)
                            @php
                                $totalComprobado = $anticipo->comprobantes->sum('monto');
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
                                    {{ $anticipo->tipo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $anticipo->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    S/ {{ number_format($anticipo->importe, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    S/ {{ number_format($totalComprobado, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($anticipo->estado === 'completo')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Completo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="toggleAnticipoUpload({{ $anticipo->id }})"
                                            class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-xs flex items-center space-x-1">
                                        <i class="fas fa-file-upload"></i>
                                        <span>Subir comprobante</span>
                                    </button>
                                </td>
                            </tr>
                            <tr id="anticipo-upload-{{ $anticipo->id }}" class="hidden bg-blue-50/50">
                                <td colspan="6" class="px-6 py-4">
                                    <form action="{{ route('anticipos.comprobantes.store', $anticipo->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="form_type" value="anticipo_comprobante">
                                        <input type="hidden" name="anticipo_target" value="{{ $anticipo->id }}">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de comprobante *</label>
                                                <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo') border-red-500 @enderror" required>
                                                    <option value="">Seleccione tipo</option>
                                                    @foreach($tiposComprobante as $tipo)
                                                        <option value="{{ $tipo->codigo }}" {{ old('tipo') == $tipo->codigo ? 'selected' : '' }}>
                                                            {{ $tipo->descripcion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('tipo')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                                                <input type="date" name="fecha" value="{{ old('fecha') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha') border-red-500 @enderror">
                                                @error('fecha')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Monto (S/.) *</label>
                                                <input type="number" step="0.01" name="monto" value="{{ old('monto') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto') border-red-500 @enderror">
                                                @error('monto')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Archivo (PDF/Imagen) *</label>
                                                <input type="file" name="archivo" accept="image/*,application/pdf" required class="w-full text-sm text-gray-500 @error('archivo') border border-red-500 @enderror">
                                                @error('archivo')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Detalle</label>
                                                <textarea name="detalle" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('detalle') border-red-500 @enderror">{{ old('detalle') }}</textarea>
                                                @error('detalle')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-end space-x-3">
                                            <button type="button" onclick="toggleAnticipoUpload({{ $anticipo->id }})" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition text-sm">Cancelar</button>
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                                <i class="fas fa-upload mr-1"></i>Registrar comprobante
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                No tienes anticipos asignados.
            </div>
        @endif
    </div>
    @endif

    <!-- Comprobantes Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Comprobantes Registrados</h3>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Buscar comprobantes..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($comprobantes->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay comprobantes registrados aún</p>
                <a href="{{ route('comprobantes.create') }}" 
                   class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Comprobante
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                @endif
                            @endauth
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comprobantes as $comprobante)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $comprobante->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->tipo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->fecha }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">S/ {{ number_format($comprobante->monto, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $comprobante->detalle ?? '-' }}</td>
                                @auth
                                    @if(Auth::user()->isAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->user->name ?? '-' }}</td>
                                    @endif
                                @endauth
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($comprobante->estado === 'aprobado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Aprobado
                                        </span>
                                    @elseif($comprobante->estado === 'rechazado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rechazado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($comprobante->archivo)
                                        <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                            <i class="fas fa-file mr-1"></i>Ver
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Sin archivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('comprobantes.show', $comprobante->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded transition">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @auth
                                            @if(!Auth::user()->isAdmin())
                                                <a href="{{ route('comprobantes.edit', $comprobante->id) }}" 
                                                   class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded transition">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        @endauth
                                        @auth
                                            @if(!Auth::user()->isAdmin() && $comprobante->user_id === Auth::id())
                                                <form action="{{ route('comprobantes.destroy', $comprobante->id) }}" 
                                                      method="POST"
                                                      class="inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded transition">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@if(Auth::user()->isOperador())
<script>
    function hideAnticipoUploads() {
        document.querySelectorAll('[id^="anticipo-upload-"]').forEach(row => row.classList.add('hidden'));
    }

    function toggleAnticipoUpload(id) {
        const row = document.getElementById('anticipo-upload-' + id);
        const isHidden = row.classList.contains('hidden');
        hideAnticipoUploads();
        if (isHidden) {
            row.classList.remove('hidden');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    @if($errors->any() && old('form_type') === 'anticipo_comprobante' && old('anticipo_target'))
        document.getElementById('anticipo-upload-{{ old('anticipo_target') }}')?.classList.remove('hidden');
    @endif
</script>
@endif
