@extends('layouts.app')

@section('title', 'Detalles del Anticipo')
@section('subtitle', 'Revisión y gestión del anticipo/reembolso')

@section('header-actions')
    @auth
        @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && !in_array($anticipo->estado, ['aprobado', 'rechazado']))
            <div class="flex items-center space-x-3">
                @if(in_array($anticipo->estado, ['pendiente', 'en_observacion']))
                    <a href="{{ route('anticipos.edit', $anticipo->id) }}"
                       class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-medium">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </a>
                @endif
                <form action="{{ route('anticipos.destroy', $anticipo->id) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('¿Estás seguro de que deseas eliminar este anticipo? Esta acción eliminará también todos los comprobantes asociados.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-medium">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                </form>
                {{-- <a href="{{ route('anticipos.export.excel', $anticipo->id) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a> --}}
                <a href="{{ route('anticipos.export.pdf', $anticipo->id) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </a>
                <button type="button" 
                        onclick="openRejectModal()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    <i class="fas fa-times mr-2"></i>Rechazar
                </button>
                <button type="button" 
                        onclick="openApproveModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-check mr-2"></i>Aprobar
                </button>
            </div>
        @else
            <div class="flex items-center space-x-3">
                {{-- <a href="{{ route('anticipos.export.excel', $anticipo->id) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a> --}}
                <a href="{{ route('anticipos.export.pdf', $anticipo->id) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </a>
            </div>
        @endif
    @endauth
@endsection

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex items-center space-x-2 text-sm text-gray-600">
            <a href="{{ route('comprobantes.index') }}" class="hover:text-gray-900">← Dashboard</a>
            <span>/</span>
            <span class="text-gray-900">{{ ucfirst($anticipo->tipo) }} #{{ $anticipo->id }}</span>
        </nav>
    </div>

    <!-- Header con número y estado -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ ucfirst($anticipo->tipo) }} #{{ $anticipo->id }}
            </h1>
            <div>
                @if($anticipo->estado === 'completo')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Completo
                    </span>
                @elseif($anticipo->estado === 'aprobado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-check-circle mr-1"></i>Aprobado
                    </span>
                @elseif($anticipo->estado === 'rechazado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>Rechazado
                    </span>
                @elseif($anticipo->estado === 'en_observacion')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-eye mr-1"></i>En observación
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                    </span>
                @endif
            </div>
        </div>
    </div>

@php
    $simbolo = match($anticipo->moneda) {
        'dolares' => '$',
        'euros' => '€',
        default => 'S/.'
    };
@endphp
    <!-- ... existing header code ... -->

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Columna izquierda - Información del anticipo -->
        <div class="flex-1 lg:w-3/5 space-y-6">
            <!-- Información del anticipo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- ... -->
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Importe</p>
                        <p class="text-lg font-bold text-gray-900">{{ $simbolo }} {{ number_format($anticipo->importe, 2) }}</p>
                    </div>
                    <!-- ... -->
            </div>

            <!-- Progreso del anticipo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Progreso</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Total Aprobado</span>
                            <span class="text-lg font-bold text-green-600">{{ $simbolo }} {{ number_format($totalComprobado, 2) }}</span>
                        </div>
                        @if($totalRechazado > 0)
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Total Rechazado</span>
                                <span class="text-lg font-bold text-red-600">{{ $simbolo }} {{ number_format($totalRechazado, 2) }}</span>
                            </div>
                        @endif
                        <!-- ... -->
                        <div class="flex items-center justify-between mt-2 text-sm text-gray-600">
                            <span>{{ number_format($porcentaje, 1) }}% completado</span>
                            @if($restante > 0)
                                <span>Falta: {{ $simbolo }} {{ number_format($restante, 2) }}</span>
                            @elseif($restante < 0)
                                <span class="text-red-600 font-medium">Excedente: {{ $simbolo }} {{ number_format(abs($restante), 2) }}</span>
                            @else
                                <span class="text-green-600 font-medium">Completo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comprobantes asociados -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- ... -->
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <!-- ... -->
                                @foreach($anticipo->comprobantes as $comprobante)
                                    <tr class="hover:bg-gray-50">
                                        <!-- ... -->
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $simbolo }} {{ number_format($comprobante->monto, 2) }}</td>
                                        <!-- ... -->
            </div>
        </div>

        <!-- Columna derecha - Resumen y acciones -->
        <div class="lg:w-2/5 lg:min-w-[400px]">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <span class="text-sm text-gray-600">Importe Otorgado</span>
                        <span class="text-lg font-bold text-gray-900">{{ $simbolo }} {{ number_format($anticipo->importe, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <span class="text-sm text-gray-600">Importe Aprobado</span>
                        <span class="text-lg font-semibold text-blue-600">{{ $simbolo }} {{ number_format($totalComprobado, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Saldo a Reembolsar</span>
                        <span class="text-lg font-semibold {{ $restante < 0 ? 'text-red-600' : ($restante > 0 ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ $simbolo }} {{ number_format($restante, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para Aprobar/Rechazar -->
    @auth
        @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && in_array($anticipo->estado, ['pendiente', 'completo', 'en_observacion']))
            <!-- Modal Aprobar -->
            <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Aprobar Anticipo</h3>
                    <form action="{{ route('anticipos.aprobar', $anticipo->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mensaje de aprobación (obligatorio):
                            </label>
                            <textarea name="mensaje" 
                                      rows="4"
                                      required
                                      minlength="2"
                                      title="El mensaje debe tener al menos 2 caracteres"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('mensaje') border-red-500 @enderror"
                                      placeholder="Escribe un mensaje de aprobación...">{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" 
                                    onclick="closeApproveModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                <i class="fas fa-check mr-2"></i>Aprobar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Rechazar -->
            <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Rechazar Anticipo</h3>
                    <form action="{{ route('anticipos.rechazar', $anticipo->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo del rechazo (obligatorio):
                            </label>
                            <textarea name="mensaje" 
                                      rows="4"
                                      required
                                      minlength="2"
                                      title="El mensaje debe tener al menos 2 caracteres"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 @error('mensaje') border-red-500 @enderror"
                                      placeholder="Explica el motivo del rechazo...">{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" 
                                    onclick="closeRejectModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                                <i class="fas fa-times mr-2"></i>Rechazar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <script>
        function openApproveModal() {
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }

        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Cerrar modales al hacer click fuera
        document.getElementById('approveModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeApproveModal();
            }
        });

        document.getElementById('rejectModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
@endsection

