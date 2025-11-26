@extends('layouts.app')

@section('title', 'Tipos de Comprobante')
@section('subtitle', 'Gestión de tipos de comprobante')

@section('content')
<div class="space-y-6">
    <!-- Header con botón crear -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Tipos de Comprobante</h2>
            <p class="text-gray-600 mt-1">Gestiona los tipos de comprobante del sistema</p>
        </div>
        <a href="{{ route('tipos-comprobante.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
            <i class="fas fa-plus mr-2"></i>Nuevo Tipo
        </a>
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

    <!-- Tabla de tipos de comprobante -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider max-w-md">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tiposComprobante as $tipo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $tipo->codigo }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <span class="text-sm text-gray-900">{{ Str::limit($tipo->descripcion, 80) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($tipo->activo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-times-circle mr-1"></i>Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('tipos-comprobante.edit', $tipo->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tipos-comprobante.destroy', $tipo->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este tipo de comprobante?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>No hay tipos de comprobante registrados</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

