@extends('layouts.app')

@section('title', 'Anticipos y Reembolsos')
@section('subtitle', 'Anticipos y reembolsos de ' . $user->name)

@section('content')
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex items-center space-x-2 text-sm text-gray-600">
            <a href="{{ route('comprobantes.index') }}" class="hover:text-gray-900">← Dashboard</a>
            <span>/</span>
            <span class="text-gray-900">{{ $user->name }}</span>
        </nav>
    </div>

    <!-- Información del Usuario -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    DNI: {{ $user->dni ?? 'N/A' }} | 
                    Email: {{ $user->email ?? 'N/A' }}
                </p>
            </div>
            <div>
                <a href="{{ route('areas.users.anticipos.create', [Auth::user()->area_id, $user->id]) }}"
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-plus mr-2"></i>Nuevo Anticipo/Reembolso
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Anticipos y Reembolsos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Anticipos y Reembolsos</h3>
            <p class="text-sm text-gray-500 mt-1">Haz clic en un anticipo para ver sus comprobantes</p>
        </div>

        @if($anticipos->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay anticipos o reembolsos registrados para este usuario</p>
                <a href="{{ route('areas.users.anticipos.create', [Auth::user()->area_id, $user->id]) }}"
                   class="mt-4 inline-block px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Crear primer anticipo/reembolso
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobantes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($anticipos as $anticipo)
                            @php
                                $totalComprobado = $anticipo->comprobantes->where('estado', 'aprobado')->sum('monto');
                                $totalComprobantes = $anticipo->comprobantes->count();
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($anticipo->tipo === 'anticipo')
                                            <i class="fas fa-arrow-down text-green-600 mr-2"></i>
                                        @else
                                            <i class="fas fa-arrow-up text-blue-600 mr-2"></i>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 capitalize">
                                            {{ $anticipo->tipo === 'anticipo' ? 'Anticipo' : 'Reembolso' }}
                                        </span>
                                    </div>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $totalComprobantes }} comprobante{{ $totalComprobantes !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($anticipo->estado === 'aprobado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Aprobado
                                        </span>
                                    @elseif($anticipo->estado === 'rechazado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rechazado
                                        </span>
                                    @elseif($anticipo->estado === 'en_observacion')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-eye mr-1"></i>En observación
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('anticipos.show', $anticipo->id) }}" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>Ver Detalles
                                        </a>
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

