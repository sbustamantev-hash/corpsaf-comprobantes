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
