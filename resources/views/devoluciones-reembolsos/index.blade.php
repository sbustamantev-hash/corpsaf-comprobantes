@extends('layouts.app')

@section('title', 'Devoluciones y Reembolsos')
@section('subtitle', Auth::user()->isOperador() ? 'Mis devoluciones' : 'Gestión de devoluciones y reembolsos')

@section('content')
    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Tabla de devoluciones y reembolsos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ Auth::user()->isOperador() ? 'Mis Devoluciones' : 'Devoluciones y Reembolsos' }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if(Auth::user()->isAdmin() || Auth::user()->isAreaAdmin())
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anticipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($devolucionesReembolsos as $dr)
                            <tr class="hover:bg-gray-50">
                                @if(Auth::user()->isAdmin() || Auth::user()->isAreaAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $dr->anticipo->usuario->name ?? '-' }}
                                        <p class="text-xs text-gray-500">{{ $dr->anticipo->usuario->dni ?? '-' }}</p>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dr->tipo === 'devolucion' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($dr->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <a href="{{ route('anticipos.show', $dr->anticipo_id) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ ucfirst($dr->anticipo->tipo) }} #{{ $dr->anticipo_id }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if($dr->metodo_pago === 'deposito_cuenta')
                                        @if($dr->banco)
                                            {{ $dr->banco->descripcion }}
                                        @elseif($dr->billetera_digital)
                                            {{ \App\Models\DevolucionReembolso::getBilleterasDigitales()[$dr->billetera_digital] }}
                                        @endif
                                        @if($dr->numero_operacion)
                                            <br><span class="text-xs text-gray-500">Op: {{ $dr->numero_operacion }}</span>
                                        @endif
                                    @else
                                        Depósito en caja
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    @php
                                        $simbolo = match($dr->moneda) {
                                            'dolares' => '$',
                                            'euros' => '€',
                                            default => 'S/.'
                                        };
                                    @endphp
                                    {{ $simbolo }} {{ number_format($dr->importe, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $dr->fecha_deposito ? $dr->fecha_deposito->format('d/m/Y') : ($dr->fecha_devolucion ? $dr->fecha_devolucion->format('d/m/Y') : '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($dr->estado === 'aprobado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Aprobado
                                        </span>
                                    @elseif($dr->estado === 'rechazado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rechazado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('anticipos.show', $dr->anticipo_id) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded transition"
                                           title="Ver anticipo">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($dr->archivo)
                                            <a href="{{ Storage::url($dr->archivo) }}" target="_blank"
                                               class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded transition"
                                               title="Ver archivo">
                                                <i class="fas fa-file"></i>
                                            </a>
                                        @endif
                                        @auth
                                            @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && $dr->estado === 'pendiente')
                                                <form action="{{ route('devoluciones-reembolsos.aprobar', $dr->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded transition"
                                                            title="Aprobar"
                                                            onclick="return confirm('¿Estás seguro de que deseas aprobar esta {{ $dr->tipo }}?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('devoluciones-reembolsos.rechazar', $dr->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded transition"
                                                            title="Rechazar"
                                                            onclick="return confirm('¿Estás seguro de que deseas rechazar esta {{ $dr->tipo }}?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() || Auth::user()->isAreaAdmin() ? '8' : '7' }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No hay {{ Auth::user()->isOperador() ? 'devoluciones' : 'devoluciones ni reembolsos' }} registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

