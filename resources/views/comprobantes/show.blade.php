@extends('layouts.app')

@section('title', 'Detalles del Comprobante')
@section('subtitle', 'Revisión y gestión del comprobante')

@section('header-actions')
    @auth
        @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && in_array($comprobante->estado, ['pendiente', 'en_observacion']))
            <div class="flex items-center space-x-3">
                <button type="button" 
                        onclick="openRejectModal()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                    Rechazar
                </button>
                <button type="button" 
                        onclick="openApproveModal()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    Aprobar
                </button>
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
        <a href="{{ route('comprobantes.index') }}" class="hover:text-gray-900">← Todos los Comprobantes</a>
        <span>/</span>
        <span class="text-gray-900">Comprobante #{{ $comprobante->id }}</span>
    </nav>
</div>

<!-- Header con número y estado -->
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <h1 class="text-3xl font-bold text-gray-900">Comprobante #{{ $comprobante->id }}</h1>
        <div>
            @if($comprobante->estado === 'aprobado')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    Aprobado
                </span>
            @elseif($comprobante->estado === 'rechazado')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    Rechazado
                </span>
            @elseif($comprobante->estado === 'en_observacion')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    En observación
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    Pendiente
                </span>
            @endif
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-6">
    <!-- Columna izquierda - Información del comprobante -->
    <div class="flex-1 lg:w-3/5 space-y-6">
        <!-- Información de envío -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Comprobante</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Enviado por</p>
                    <p class="text-base font-medium text-gray-900">{{ $comprobante->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Fecha de envío</p>
                    <p class="text-base font-medium text-gray-900">{{ $comprobante->created_at->format('d M, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Tipo</p>
                    <p class="text-base font-medium text-gray-900">{{ $comprobante->tipo }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Monto</p>
                    <p class="text-lg font-bold text-gray-900">S/ {{ number_format($comprobante->monto, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Fecha del comprobante</p>
                    <p class="text-base font-medium text-gray-900">{{ $comprobante->fecha }}</p>
                </div>
            </div>
        </div>

        <!-- Mensaje del usuario -->
        @if($comprobante->detalle)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Mensaje del Usuario</h2>
                <p class="text-gray-700 leading-relaxed">{{ $comprobante->detalle }}</p>
            </div>
        @endif

        <!-- Archivos adjuntos -->
        @if($comprobante->archivo)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Archivos Adjuntos</h2>
                <div class="flex flex-wrap gap-4">
                    @if(in_array(strtolower(pathinfo($comprobante->archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                        <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                           target="_blank"
                           class="block">
                            <img src="{{ route('comprobantes.download', $comprobante->id) }}" 
                                 alt="Comprobante"
                                 class="w-24 h-24 object-cover rounded-lg border border-gray-200 hover:border-blue-400 transition cursor-pointer shadow-sm">
                        </a>
                    @else
                        <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                           target="_blank"
                           class="flex items-center space-x-2 p-2 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-pdf text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-xs">Ver PDF</p>
                                <p class="text-xs text-gray-500">Click para abrir</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Columna derecha - Observaciones y comunicación (más ancho) -->
    <div class="lg:w-2/5 lg:min-w-[400px]">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col sticky top-6" style="max-height: calc(100vh - 150px);">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Conversación</h2>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @if($comprobante->observaciones->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-comment-slash text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 text-sm">No hay mensajes aún</p>
                    </div>
                @else
                    @foreach($comprobante->observaciones as $observacion)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm
                                    @if($observacion->tipo === 'aprobacion') bg-green-600
                                    @elseif($observacion->tipo === 'rechazo') bg-red-600
                                    @else bg-blue-600
                                    @endif">
                                    @if($observacion->user->isAdmin())
                                        <i class="fas fa-shield-alt text-xs"></i>
                                    @else
                                        {{ strtoupper(substr($observacion->user->name, 0, 1)) }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-medium text-gray-900 text-sm">{{ $observacion->user->name }}</span>
                                    @if($observacion->tipo === 'aprobacion')
                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-800 rounded-full">Aprobación</span>
                                    @elseif($observacion->tipo === 'rechazo')
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-800 rounded-full">Rechazo</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mb-1">{{ $observacion->created_at->format('M d, Y - h:i A') }}</p>
                                @if($observacion->mensaje)
                                    <p class="text-sm text-gray-700 leading-relaxed mb-2">{{ $observacion->mensaje }}</p>
                                @endif
                                @if($observacion->archivo)
                                    <div class="mt-2">
                                        @if(in_array(strtolower(pathinfo($observacion->archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                            <a href="{{ route('observaciones.download', $observacion->id) }}" 
                                               target="_blank"
                                               class="block">
                                                <img src="{{ route('observaciones.download', $observacion->id) }}" 
                                                     alt="Archivo adjunto"
                                                     class="w-20 h-20 object-cover rounded-lg border border-gray-200 hover:border-blue-400 transition cursor-pointer shadow-sm">
                                            </a>
                                        @else
                                            <a href="{{ route('observaciones.download', $observacion->id) }}" 
                                               target="_blank"
                                               class="inline-flex items-center space-x-1.5 p-1.5 border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition">
                                                <div class="w-6 h-6 bg-red-100 rounded flex items-center justify-center">
                                                    <i class="fas fa-file-pdf text-red-600 text-xs"></i>
                                                </div>
                                                <span class="text-xs font-medium text-gray-900">Ver archivo</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <form action="{{ route('comprobantes.observacion', $comprobante->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               name="mensaje"
                               id="mensaje-input"
                               placeholder="Agregar un comentario o solicitar información..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm @error('mensaje') border-red-500 @enderror">
                        @error('mensaje')
                            <p class="text-xs text-red-600 absolute">{{ $message }}</p>
                        @enderror
                        <label for="archivo-input" class="w-10 h-10 bg-gray-200 text-gray-600 rounded-full hover:bg-gray-300 transition flex items-center justify-center flex-shrink-0 cursor-pointer">
                            <i class="fas fa-paperclip text-sm"></i>
                        </label>
                        <input type="file" 
                               name="archivo" 
                               id="archivo-input" 
                               accept="image/*,application/pdf"
                               class="hidden"
                               onchange="updateFileName(this)">
                        <button type="submit" class="w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                    <div id="file-name" class="text-xs text-gray-500 hidden"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modales para Aprobar/Rechazar -->
@auth
    @if((Auth::user()->isAdmin() || Auth::user()->isAreaAdmin()) && in_array($comprobante->estado, ['pendiente', 'en_observacion']))
        <!-- Modal Aprobar -->
        <div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Aprobar Comprobante</h3>
                <form action="{{ route('comprobantes.aprobar', $comprobante->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mensaje de aprobación (obligatorio):
                        </label>
                        <textarea name="mensaje" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('mensaje') border-red-500 @enderror" 
                                  required 
                                  placeholder="Escribe el motivo de la aprobación..."></textarea>
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
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            <i class="fas fa-check mr-2"></i>Aprobar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Rechazar -->
        <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Rechazar Comprobante</h3>
                <form action="{{ route('comprobantes.rechazar', $comprobante->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mensaje de rechazo (obligatorio):
                        </label>
                        <textarea name="mensaje" 
                                  rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 @error('mensaje') border-red-500 @enderror" 
                                  required 
                                  placeholder="Escribe el motivo del rechazo..."></textarea>
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
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-times mr-2"></i>Rechazar
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
            // Cerrar modal al hacer click fuera
            document.getElementById('approveModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeApproveModal();
            });
            document.getElementById('rejectModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeRejectModal();
            });
            
            // Mostrar nombre del archivo seleccionado
            function updateFileName(input) {
                const fileNameDiv = document.getElementById('file-name');
                if (input.files && input.files[0]) {
                    fileNameDiv.textContent = 'Archivo seleccionado: ' + input.files[0].name;
                    fileNameDiv.classList.remove('hidden');
                } else {
                    fileNameDiv.classList.add('hidden');
                }
            }
        </script>
    @endif
@endauth
@endsection
