@extends('layouts.app')

@section('title', 'Editar Comprobante')
@section('subtitle', 'Modifica los datos del comprobante')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('comprobantes.update', $comprobante->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Nombre del trabajador (solo lectura) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del trabajador</label>
                    <input type="text" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed"
                           value="{{ $comprobante->user->name ?? 'N/A' }}"
                           readonly
                           disabled>
                    <p class="mt-1 text-xs text-gray-500">El trabajador no puede ser modificado</p>
                </div>

                {{-- RUC de la Empresa --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">RUC de la Empresa <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="ruc_empresa"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ruc_empresa') border-red-500 @enderror"
                           value="{{ old('ruc_empresa', $comprobante->ruc_empresa) }}"
                           placeholder="Ingresa el RUC de tu empresa">
                    <p class="mt-1 text-xs text-gray-500">Corresponde a la empresa que te brinda el servicio</p>
                    @error('ruc_empresa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de comprobante <span class="text-red-500">*</span></label>
                    <select name="tipo" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo') border-red-500 @enderror">
                        <option value="">Selecciona un tipo</option>
                        @foreach($tiposComprobante as $tipo)
                            <option value="{{ $tipo->codigo }}" {{ old('tipo', $comprobante->tipo) == $tipo->codigo ? 'selected' : '' }}>
                                [{{ $tipo->codigo }}] {{ $tipo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Concepto --}}
                @php
                    $conceptoActualId = old('concepto', $comprobante->concepto_id);
                    $esOtros = $comprobante->concepto && strtoupper($comprobante->concepto->nombre) === 'OTROS';
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Concepto <span class="text-red-500">*</span></label>
                    <select name="concepto" id="concepto-select" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('concepto') border-red-500 @enderror">
                        <option value="">Selecciona un concepto</option>
                        @foreach($conceptos as $concepto)
                            <option value="{{ $concepto->id }}" {{ $conceptoActualId == $concepto->id ? 'selected' : '' }}>
                                {{ $concepto->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('concepto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div id="concepto-otro-field" class="{{ $esOtros ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Especificar concepto <span class="text-red-500">*</span></label>
                    <input type="text" name="concepto_otro" id="concepto-otro-input"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('concepto_otro') border-red-500 @enderror"
                        value="{{ old('concepto_otro', $comprobante->concepto_otro) }}"
                        placeholder="Ingresa el concepto"
                        {{ $esOtros ? 'required' : '' }}>
                    @error('concepto_otro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Serie y Número --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número de serie <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="serie"
                               maxlength="4"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('serie') border-red-500 @enderror"
                               value="{{ old('serie', $comprobante->serie) }}"
                               placeholder="Ej: 0846">
                        @error('serie')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número de comprobante <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="numero"
                               maxlength="10"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('numero') border-red-500 @enderror"
                               value="{{ old('numero', $comprobante->numero) }}"
                               placeholder="Ej: 0000000456">
                        <p class="mt-1 text-xs text-gray-500">10 dígitos. Se completa con ceros.</p>
                        @error('numero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Fecha y Monto --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del comprobante <span class="text-red-500">*</span></label>
                        <input type="date" 
                               name="fecha" 
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha') border-red-500 @enderror"
                               value="{{ old('fecha', $comprobante->fecha ? $comprobante->fecha->format('Y-m-d') : '') }}">
                        @error('fecha')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto <span class="text-red-500">*</span></label>
                        <input type="number" 
                               name="monto" 
                               step="0.01"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto') border-red-500 @enderror"
                               value="{{ old('monto', $comprobante->monto) }}">
                        @error('monto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Moneda --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Moneda <span class="text-red-500">*</span></label>
                    <select name="moneda" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('moneda') border-red-500 @enderror">
                        <option value="soles" {{ old('moneda', $comprobante->moneda ?? 'soles') == 'soles' ? 'selected' : '' }}>Soles (S/.)</option>
                        <option value="dolares" {{ old('moneda', $comprobante->moneda ?? 'soles') == 'dolares' ? 'selected' : '' }}>Dólares ($)</option>
                        <option value="euros" {{ old('moneda', $comprobante->moneda ?? 'soles') == 'euros' ? 'selected' : '' }}>Euros (€)</option>
                    </select>
                    @error('moneda')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Detalle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Detalle / Observaciones (opcional)</label>
                    <textarea name="detalle" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('detalle') border-red-500 @enderror"
                              placeholder="Observaciones o comentarios adicionales...">{{ old('detalle', $comprobante->detalle) }}</textarea>
                    @error('detalle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campos de Origen y Destino (solo para Planilla de Movilidad) --}}
                <div id="movilidad-fields" class="{{ $comprobante->tipoComprobante() && stripos($comprobante->tipoComprobante()->descripcion, 'Planilla de Movilidad') !== false ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Origen <span class="text-red-500">*</span></label>
                            <input type="text" name="origen" id="origen-input"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('origen') border-red-500 @enderror"
                                value="{{ old('origen', $comprobante->origen) }}" placeholder="Ej: Lima, San Isidro">
                            @error('origen')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destino <span class="text-red-500">*</span></label>
                            <input type="text" name="destino" id="destino-input"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('destino') border-red-500 @enderror"
                                value="{{ old('destino', $comprobante->destino) }}" placeholder="Ej: Lima, Miraflores">
                            @error('destino')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Archivo actual --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Archivo actual</label>
                    @if($comprobante->archivo)
                        <div class="mb-3">
                            <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-file mr-2"></i>Ver Archivo Actual
                            </a>
                        </div>
                        @if(in_array(strtolower(pathinfo($comprobante->archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                            <div class="mt-2">
                                <img src="{{ route('comprobantes.download', $comprobante->id) }}" 
                                     class="max-w-xs rounded-lg border border-gray-200"
                                     alt="Archivo actual">
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Sin archivo</p>
                    @endif
                </div>

                {{-- Cambiar archivo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cambiar archivo (opcional)</label>
                    <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition cursor-pointer">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="archivo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                    <span>Subir nuevo archivo</span>
                                    <input id="archivo" name="archivo" type="file" class="sr-only" accept="image/*,application/pdf" onchange="handleFileSelect(this)">
                                </label>
                                <p class="pl-1">o arrastra y suelta</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 40MB</p>
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
                    <i class="fas fa-save mr-2"></i>Actualizar Comprobante
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

    // Manejar campo "Concepto Otros"
    const conceptoSelect = document.getElementById('concepto-select');
    const conceptoOtroField = document.getElementById('concepto-otro-field');
    const conceptoOtroInput = document.getElementById('concepto-otro-input');

    if (conceptoSelect) {
        function checkConceptoOtros() {
            const selectedOption = conceptoSelect.options[conceptoSelect.selectedIndex];
            const conceptoNombre = selectedOption ? selectedOption.text.trim().toUpperCase() : '';
            
            if (conceptoNombre === 'OTROS') {
                conceptoOtroField.classList.remove('hidden');
                conceptoOtroInput.required = true;
            } else {
                conceptoOtroField.classList.add('hidden');
                conceptoOtroInput.required = false;
                conceptoOtroInput.value = '';
            }
        }

        conceptoSelect.addEventListener('change', checkConceptoOtros);
    }

    // Manejar campos de Origen y Destino para Planilla de Movilidad
    const tipoSelect = document.querySelector('select[name="tipo"]');
    const movilidadFields = document.getElementById('movilidad-fields');
    const origenInput = document.getElementById('origen-input');
    const destinoInput = document.getElementById('destino-input');
    const monedaSelect = document.querySelector('select[name="moneda"]');
    const montoInput = document.querySelector('input[name="monto"]');
    const rmv = {{ $rmv ?? 1130 }};
    const maxMontoPlanilla = rmv * 0.04;

    function toggleMovilidadFields() {
        const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
        const tipoTexto = selectedOption ? selectedOption.text.toUpperCase() : '';
        
        if (tipoTexto.includes('PLANILLA DE MOVILIDAD')) {
            // Mostrar campos de origen y destino
            if (movilidadFields) {
                movilidadFields.classList.remove('hidden');
            }
            if (origenInput) origenInput.required = true;
            if (destinoInput) destinoInput.required = true;

            // Forzar Soles
            if (monedaSelect) {
                monedaSelect.value = 'soles';
                monedaSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
                Array.from(monedaSelect.options).forEach(opt => {
                    if (opt.value !== 'soles') opt.disabled = true;
                });
            }

            // Validar monto
            if (montoInput && parseFloat(montoInput.value) > maxMontoPlanilla) {
                alert(`El monto máximo para Planilla de Movilidad es del 4% del RMV (S/ ${maxMontoPlanilla.toFixed(2)})`);
                montoInput.value = maxMontoPlanilla.toFixed(2);
            }
        } else {
            // Ocultar campos de origen y destino
            if (movilidadFields) {
                movilidadFields.classList.add('hidden');
            }
            if (origenInput) {
                origenInput.required = false;
                if (!origenInput.value) origenInput.value = '';
            }
            if (destinoInput) {
                destinoInput.required = false;
                if (!destinoInput.value) destinoInput.value = '';
            }

            // Restaurar moneda
            if (monedaSelect) {
                monedaSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
                Array.from(monedaSelect.options).forEach(opt => {
                    opt.disabled = false;
                });
            }
        }
    }

    if (tipoSelect) {
        tipoSelect.addEventListener('change', toggleMovilidadFields);
        toggleMovilidadFields(); // Ejecutar al cargar para establecer estado inicial
    }

    // Validar monto al cambiar
    if (montoInput && tipoSelect) {
        montoInput.addEventListener('blur', function() {
            const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
            const tipoTexto = selectedOption ? selectedOption.text.toUpperCase() : '';
            if (tipoTexto.includes('PLANILLA DE MOVILIDAD')) {
                const monto = parseFloat(montoInput.value) || 0;
                if (monto > maxMontoPlanilla) {
                    alert(`El monto máximo para Planilla de Movilidad es del 4% del RMV (S/ ${maxMontoPlanilla.toFixed(2)})`);
                    montoInput.value = maxMontoPlanilla.toFixed(2);
                }
            }
        });
    }
</script>
@endsection
