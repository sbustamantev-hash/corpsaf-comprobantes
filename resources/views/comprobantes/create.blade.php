@extends('layouts.app')

@section('title', 'Nuevo Comprobante')
@section('subtitle', 'Registra un nuevo comprobante')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @if(isset($anticipo))
                <div class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg">
                    <p class="text-sm text-blue-800 font-semibold">Subiendo comprobante para:</p>
                    <p class="text-lg font-bold text-blue-900 mt-1">{{ ucfirst($anticipo->tipo) }} del
                        {{ $anticipo->fecha->format('d/m/Y') }}
                    </p>
                    <p class="text-sm text-blue-700">Monto asignado: S/ {{ number_format($anticipo->importe, 2) }}</p>
                </div>
            @endif

            <form action="{{ route('comprobantes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($anticipo))
                    <input type="hidden" name="anticipo_id" value="{{ $anticipo->id }}">
                @endif

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">RUC de la Empresa</label>
                        <input type="text" name="ruc_empresa"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruc_empresa') border-red-500 @enderror"
                            value="{{ old('ruc_empresa') }}" placeholder="Ingresa el RUC de tu empresa">
                        <p class="mt-1 text-xs text-gray-500">Corresponde a la empresa que te brinda el servicio</p>
                        @error('ruc_empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Serie de Comprobante</label>
                            <input type="text" name="serie" maxlength="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('serie') border-red-500 @enderror"
                                value="{{ old('serie') }}" placeholder="Ej: 0846">
                            @error('serie')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de comprobante</label>
                            <input type="text" name="numero" maxlength="10"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('numero') border-red-500 @enderror"
                                value="{{ old('numero') }}" placeholder="Ej: 0000000456">
                            <p class="mt-1 text-xs text-gray-500">10 dígitos. Se completará con ceros a la izquierda.</p>
                            @error('numero')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de comprobante</label>
                        <select name="tipo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo') border-red-500 @enderror"
                            required>
                            <option value="">Selecciona un tipo</option>
                            @foreach($tiposComprobante as $tipo)
                                <option value="{{ $tipo->codigo }}" {{ old('tipo') == $tipo->codigo ? 'selected' : '' }}>
                                    [{{ $tipo->codigo }}] {{ $tipo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto (S/.)</label>
                            <input type="number" step="0.01" name="monto"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto') border-red-500 @enderror"
                                value="{{ old('monto') }}" required placeholder="0.00">
                            @error('monto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" name="fecha"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha') border-red-500 @enderror"
                                value="{{ old('fecha') }}" required>
                            @error('fecha')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Detalle (opcional)</label>
                        <textarea name="detalle" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('detalle') border-red-500 @enderror"
                            placeholder="Descripción adicional del comprobante...">{{ old('detalle') }}</textarea>
                        @error('detalle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Archivo (imagen o PDF)</label>
                        <div id="drop-zone"
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition cursor-pointer">
                            <div class="space-y-1 text-center">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="archivo"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Subir archivo</span>
                                        <input id="archivo" name="archivo" type="file" class="sr-only"
                                            accept="image/*,application/pdf" onchange="handleFileSelect(this)">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 2MB</p>
                                <p id="file-name" class="text-xs text-green-600 mt-2 hidden"></p>
                            </div>
                        </div>
                        @error('archivo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-4">
                    <a href="{{ route('comprobantes.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-save mr-2"></i>Guardar Comprobante
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('archivo');
        const fileName = document.getElementById('file-name');
        const serieInput = document.querySelector('input[name="serie"]');
        const numeroInput = document.querySelector('input[name="numero"]');

        // Prevenir comportamiento por defecto del navegador
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Efectos visuales al arrastrar
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }

        // Manejar archivos soltados
        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(fileInput);
            }
        }

        // Manejar selección de archivo
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                fileName.textContent = `Archivo seleccionado: ${file.name} (${fileSize} MB)`;
                fileName.classList.remove('hidden');
                dropZone.classList.add('border-green-400', 'bg-green-50');
            }
        }

        // Click en toda la zona para abrir el selector
        dropZone.addEventListener('click', (e) => {
            if (e.target !== fileInput && e.target.tagName !== 'LABEL') {
                fileInput.click();
            }
        });

        function padSerie(value) {
            if (!value) return '';
            const cleaned = value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            if (!cleaned.length) return '';
            return cleaned.slice(-4).padStart(4, '0');
        }

        function padNumero(value) {
            if (!value) return ''.padStart(10, '0');
            const onlyDigits = value.replace(/\D/g, '').slice(-10);
            return onlyDigits.padStart(10, '0');
        }

        if (serieInput) {
            serieInput.addEventListener('blur', () => {
                serieInput.value = padSerie(serieInput.value);
            });
        }

        if (numeroInput) {
            numeroInput.addEventListener('blur', () => {
                numeroInput.value = padNumero(numeroInput.value);
            });
        }
    </script>
@endsection