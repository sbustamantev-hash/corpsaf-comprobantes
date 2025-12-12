@extends('layouts.app')

@section('title', 'Configuraciones')
@section('subtitle', 'Personaliza el logo y nombre de la aplicación')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Configuración del Sistema</h3>
            <p class="text-sm text-gray-500 mt-1">Personaliza el logo y nombre que aparecen en la página de login</p>
        </div>

        <form action="{{ route('configuraciones.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Nombre de la Aplicación -->
            <div class="mb-6">
                <label for="nombre_app" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag mr-2 text-gray-400"></i>Nombre de la Aplicación
                </label>
                <input type="text" id="nombre_app" name="nombre_app" value="{{ old('nombre_app', $nombreApp) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre_app') border-red-500 @enderror"
                    placeholder="Ej: YnnovaCorp" required>
                @error('nombre_app')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Este nombre aparecerá en la página de login</p>
            </div>

            <!-- RMV -->
            <div class="mb-6">
                <label for="rmv" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i>Remuneración Mínima Vital (RMV)
                </label>
                <input type="number" id="rmv" name="rmv" step="0.01" min="0" value="{{ old('rmv', $rmv ?? 1130) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rmv') border-red-500 @enderror"
                    placeholder="Ej: 1130" required>
                @error('rmv')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Utilizado para calcular límites en Planilla de Movilidad (4% del RMV)
                </p>
            </div>

            <!-- Logo -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-image mr-2 text-gray-400"></i>Logo de la Aplicación
                </label>

                @if($logoPath)
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Logo actual:</p>
                        <div class="inline-block p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo actual"
                                class="max-h-32 max-w-64 object-contain"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display:none;" class="text-sm text-gray-500">
                                <i class="fas fa-exclamation-triangle mr-2"></i>No se pudo cargar el logo
                            </div>
                        </div>
                    </div>
                @endif

                <input type="file" id="logo" name="logo" accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('logo') border-red-500 @enderror">
                @error('logo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Formatos permitidos: JPG, PNG, GIF, SVG. Tamaño máximo: 40MB</p>
            </div>

            <!-- Vista Previa -->
            <div class="mb-6 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Vista Previa del Login</h4>
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-xl mb-4 overflow-hidden">
                            @if($logoPath)
                                <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo"
                                    class="w-full h-full object-contain rounded-xl"
                                    onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-file-invoice-dollar text-white text-3xl\'></i>';">
                            @else
                                <i class="fas fa-file-invoice-dollar text-white text-3xl"></i>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2" id="preview-nombre">{{ $nombreApp }}</h1>
                        <p class="text-gray-600">Liquidacion de gastos</p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('comprobantes.index') }}"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Actualizar vista previa del nombre en tiempo real
            document.getElementById('nombre_app').addEventListener('input', function (e) {
                document.getElementById('preview-nombre').textContent = e.target.value || 'YnnovaCorp';
            });
        </script>
    @endpush
@endsection