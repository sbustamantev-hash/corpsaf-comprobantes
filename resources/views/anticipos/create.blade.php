@extends('layouts.app')

@section('title', 'Nuevo Anticipo/Reembolso')
@section('subtitle', 'Registrar anticipo o reembolso para un colaborador')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('comprobantes.index') }}"
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Volver al dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Empresa: {{ $area->nombre }}</h2>
                    <p class="text-sm text-gray-500">Anticipo para {{ $user->name }} (DNI: {{ $user->dni ?? 'N/A' }})</p>
                </div>
                <div class="mt-4 md:mt-0 text-sm text-gray-500">
                    <p>Administrador: {{ Auth::user()->name }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <strong class="font-semibold">Corrige los siguientes errores:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('areas.users.anticipos.store', [$area->id, $user->id]) }}" method="POST"
                class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo <span class="text-red-500">*</span></label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="radio" name="tipo" value="anticipo" required class="text-blue-600" {{ old('tipo', 'anticipo') === 'anticipo' ? 'checked' : '' }}>
                                <span class="ml-2">Anticipo</span>
                            </label>
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="radio" name="tipo" value="reembolso" required class="text-blue-600" {{ old('tipo') === 'reembolso' ? 'checked' : '' }}>
                                <span class="ml-2">Reembolso</span>
                            </label>
                        </div>
                        @error('tipo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha" value="{{ old('fecha') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha') border-red-500 @enderror">
                        @error('fecha')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="field-tipo-rendicion">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de rendición <span class="text-red-500">*</span></label>

                        <select name="tipo_rendicion_id" id="select-tipo-rendicion" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccione un tipo de rendición</option>
                            @foreach ($tipos_rendicion as $tiporendicion)
                                <option value="{{ $tiporendicion->id }}">{{ $tiporendicion->descripcion }}</option>
                            @endforeach
                        </select>
                        @error('tipo_rendicion_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="field-banco">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entidad financiera <span class="text-red-500">*</span></label>
                        <select name="banco_id" id="select-banco" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('banco_id') border-red-500 @enderror">
                            <option value="">Seleccione banco</option>
                            @foreach($bancos as $banco)
                                <option value="{{ $banco->id }}" {{ old('banco_id') == $banco->id ? 'selected' : '' }}>
                                    {{ $banco->descripcion }}
                                </option>
                            @endforeach
                        </select>
                        @error('banco_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>



                    <div id="field-importe">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Importe (S/.) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="importe" id="input-importe" value="{{ old('importe') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('importe') border-red-500 @enderror">
                        @error('importe')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción <span class="text-red-500">*</span></label>
                    <textarea name="descripcion" rows="3" required class="w-full px-4 py-2 border border-gray-300 rounded-lg
                                                    focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500
                                                    @enderror">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('comprobantes.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Registrar Anticipo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoRadios = document.querySelectorAll('input[name="tipo"]');
            const fieldBanco = document.getElementById('field-banco');
            const fieldTipoRendicion = document.getElementById('field-tipo-rendicion');
            const inputImporte = document.getElementById('input-importe');
            const btnSubmit = document.getElementById('btn-submit');

            function toggleFields() {
                const tipo = document.querySelector('input[name="tipo"]:checked').value;
                const selectBanco = document.getElementById('select-banco');
                const selectTipoRendicion = document.getElementById('select-tipo-rendicion');
                
                if (tipo === 'reembolso') {
                    // Para reembolso: ocultar banco y tipo rendición, bloquear importe
                    fieldBanco.style.display = 'none';
                    fieldTipoRendicion.style.display = 'none';
                    if (selectBanco) {
                        selectBanco.required = false;
                        selectBanco.value = '';
                    }
                    if (selectTipoRendicion) {
                        selectTipoRendicion.required = false;
                        selectTipoRendicion.value = '';
                    }
                    inputImporte.readOnly = true;
                    inputImporte.required = false;
                    inputImporte.value = '';
                    inputImporte.classList.add('bg-gray-100', 'cursor-not-allowed');
                    inputImporte.classList.remove('focus:ring-2', 'focus:ring-blue-500');
                    btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Registrar Reembolso';
                } else {
                    // Para anticipo: mostrar todos los campos, habilitar importe
                    fieldBanco.style.display = 'block';
                    fieldTipoRendicion.style.display = 'block';
                    if (selectBanco) selectBanco.required = true;
                    if (selectTipoRendicion) selectTipoRendicion.required = true;
                    inputImporte.readOnly = false;
                    inputImporte.required = true;
                    inputImporte.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    inputImporte.classList.add('focus:ring-2', 'focus:ring-blue-500');
                    btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Registrar Anticipo';
                }
            }

            tipoRadios.forEach(radio => radio.addEventListener('change', toggleFields));

            // Ejecutar al cargar para establecer el estado inicial
            toggleFields();
        });
    </script>
@endsection