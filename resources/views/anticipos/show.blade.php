@extends('layouts.app')

@section('title', 'Detalles del Anticipo')
@section('subtitle', 'Revisión y gestión del anticipo/reembolso')

@section('header-actions')
    @auth
        @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && in_array($anticipo->estado, ['pendiente', 'completo']))
            <div class="flex items-center space-x-3">
                <a href="{{ route('anticipos.export.excel', $anticipo->id) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a>
                <a href="{{ route('anticipos.export.pdf', $anticipo->id) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                </a>
                <button type="button" 
                        onclick="openObservationModal()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-eye mr-2"></i>En observación
                </button>
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
                <a href="{{ route('anticipos.export.excel', $anticipo->id) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                </a>
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
                @if($anticipo->estado === 'aprobado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
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
                @elseif($anticipo->estado === 'completo')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Completo
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Columna izquierda - Información del anticipo -->
        <div class="flex-1 lg:w-3/5 space-y-6">
            <!-- Información del anticipo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del {{ ucfirst($anticipo->tipo) }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Usuario</p>
                        <p class="text-base font-medium text-gray-900">{{ $anticipo->usuario->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $anticipo->usuario->dni ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Empresa</p>
                        <p class="text-base font-medium text-gray-900">{{ $anticipo->area->nombre ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Fecha</p>
                        <p class="text-base font-medium text-gray-900">{{ $anticipo->fecha->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Importe</p>
                        <p class="text-lg font-bold text-gray-900">S/ {{ number_format($anticipo->importe, 2) }}</p>
                    </div>
                    @if($anticipo->banco)
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Banco</p>
                            <p class="text-base font-medium text-gray-900">{{ $anticipo->banco->descripcion ?? 'N/A' }}</p>
                        </div>
                    @endif
                    @if($anticipo->TipoRendicion)
                        <div>
                            <p class="text-sm text-gray-500 mb-1">TipoRendicion</p>
                            <p class="text-base font-medium text-gray-900">{{ $anticipo->TipoRendicion }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Creado por</p>
                        <p class="text-base font-medium text-gray-900">{{ $anticipo->creador->name ?? 'N/A' }}</p>
                    </div>
                </div>
                @if($anticipo->descripcion)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500 mb-1">Descripción</p>
                        <p class="text-gray-700 leading-relaxed">{{ $anticipo->descripcion }}</p>
                    </div>
                @endif
            </div>

            <!-- Progreso del anticipo -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Progreso</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Total Comprobado</span>
                            <span class="text-lg font-bold text-gray-900">S/ {{ number_format($totalComprobado, 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-sm text-gray-600">
                            <span>{{ number_format($porcentaje, 1) }}% completado</span>
                            @if($restante > 0)
                                <span>Falta: S/ {{ number_format($restante, 2) }}</span>
                            @elseif($restante < 0)
                                <span class="text-red-600 font-medium">Excedente: S/ {{ number_format(abs($restante), 2) }}</span>
                            @else
                                <span class="text-green-600 font-medium">Completo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comprobantes asociados -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Comprobantes Asociados ({{ $anticipo->comprobantes->count() }})</h2>
                @if($anticipo->comprobantes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($anticipo->comprobantes as $comprobante)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $comprobante->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $comprobante->tipo }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $comprobante->fecha }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">S/ {{ number_format($comprobante->monto, 2) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($comprobante->estado === 'aprobado')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Aprobado
                                                </span>
                                            @elseif($comprobante->estado === 'rechazado')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i>Rechazado
                                                </span>
                                            @elseif($comprobante->estado === 'en_observacion')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-eye mr-1"></i>En observación
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('comprobantes.show', $comprobante->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900"
                                                   title="Ver detalle y conversación">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(
                                                    Auth::id() === $comprobante->user_id &&
                                                    !in_array($comprobante->estado, ['aprobado', 'rechazado'])
                                                )
                                                    <a href="{{ route('comprobantes.edit', $comprobante->id) }}" 
                                                       class="text-yellow-600 hover:text-yellow-900"
                                                       title="Editar comprobante">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                        <p>No hay comprobantes asociados aún</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna derecha - Resumen y acciones -->
        <div class="lg:w-2/5 lg:min-w-[400px]">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <span class="text-sm text-gray-600">Importe Total</span>
                        <span class="text-lg font-bold text-gray-900">S/ {{ number_format($anticipo->importe, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <span class="text-sm text-gray-600">Total Comprobado</span>
                        <span class="text-lg font-semibold text-blue-600">S/ {{ number_format($totalComprobado, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Saldo Pendiente</span>
                        <span class="text-lg font-semibold {{ $restante < 0 ? 'text-red-600' : ($restante > 0 ? 'text-yellow-600' : 'text-green-600') }}">
                            S/ {{ number_format($restante, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para Aprobar/Rechazar -->
    @auth
        @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && in_array($anticipo->estado, ['pendiente', 'completo']))
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
                                      minlength="10"
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
                                      minlength="10"
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

            <!-- Modal En Observación -->
            <div id="observationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Poner Anticipo en Observación</h3>
                    <form action="{{ route('anticipos.observacion', $anticipo->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mensaje de observación (obligatorio):
                            </label>
                            <textarea name="mensaje" 
                                      rows="4"
                                      required
                                      minlength="10"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('mensaje') border-red-500 @enderror"
                                      placeholder="Explica qué necesita corregir el usuario...">{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center justify-end space-x-3">
                            <button type="button" 
                                    onclick="closeObservationModal()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                <i class="fas fa-eye mr-2"></i>Poner en Observación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <script>
        function openObservationModal() {
            document.getElementById('observationModal').classList.remove('hidden');
        }

        function closeObservationModal() {
            document.getElementById('observationModal').classList.add('hidden');
        }

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

