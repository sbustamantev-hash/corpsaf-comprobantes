@extends('layouts.app')

@section('title', ucfirst($tipo) === 'Devolucion' ? 'Registrar Devolución' : 'Generar Reembolso')
@section('subtitle', ucfirst($tipo) === 'Devolucion' ? 'Registrar devolución del anticipo' : 'Generar reembolso al trabajador')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('anticipos.show', $anticipo->id) }}"
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Volver al anticipo
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ ucfirst($tipo) === 'Devolucion' ? 'Registrar Devolución' : 'Generar Reembolso' }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ ucfirst($anticipo->tipo) }} #{{ $anticipo->id }} - {{ $anticipo->usuario->name }}
                    </p>
                </div>
            </div>

            @php
                $totalComprobado = $anticipo->comprobantes()->where('estado', 'aprobado')->sum('monto');
                $restante = $anticipo->importe - $totalComprobado;
                $saldoDisponible = $tipo === 'devolucion' ? $restante : abs($restante);
                $totalRegistrado = $anticipo->devolucionesReembolsos()
                    ->where('tipo', $tipo)
                    ->where('estado', 'aprobado')
                    ->sum('importe');
                $saldoFinal = $saldoDisponible - $totalRegistrado;
            @endphp

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

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

            <!-- Información del saldo -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Saldo disponible para {{ $tipo === 'devolucion' ? 'devolver' : 'reembolsar' }}:</p>
                        <p class="text-2xl font-bold text-blue-600">
                            @php
                                $simbolo = match($anticipo->moneda) {
                                    'dolares' => '$',
                                    'euros' => '€',
                                    default => 'S/.'
                                };
                            @endphp
                            {{ $simbolo }} {{ number_format($saldoFinal, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('devoluciones-reembolsos.store', $anticipo->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">

                <!-- Método de pago -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de {{ $tipo === 'devolucion' ? 'devolución' : 'reembolso' }} <span class="text-red-500">*</span></label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="radio" name="metodo_pago" value="deposito_cuenta" required class="text-blue-600" {{ old('metodo_pago', 'deposito_cuenta') === 'deposito_cuenta' ? 'checked' : '' }} onchange="toggleMetodoPago()">
                            <span class="ml-2">Depósito en cuenta</span>
                        </label>
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="radio" name="metodo_pago" value="deposito_caja" required class="text-blue-600" {{ old('metodo_pago') === 'deposito_caja' ? 'checked' : '' }} onchange="toggleMetodoPago()">
                            <span class="ml-2">Depósito en caja</span>
                        </label>
                    </div>
                    @error('metodo_pago')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campos para depósito en cuenta -->
                <div id="deposito-cuenta-fields">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Banco</label>
                            <select name="banco_id" id="banco-select"
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Billetera digital</label>
                            <select name="billetera_digital" id="billetera-select"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('billetera_digital') border-red-500 @enderror">
                                <option value="">Seleccione billetera</option>
                                @foreach(\App\Models\DevolucionReembolso::getBilleterasDigitales() as $codigo => $nombre)
                                    <option value="{{ $codigo }}" {{ old('billetera_digital') == $codigo ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">O seleccione una billetera digital</p>
                            @error('billetera_digital')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de operación <span class="text-red-500">*</span></label>
                            <input type="text" name="numero_operacion" value="{{ old('numero_operacion') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('numero_operacion') border-red-500 @enderror"
                                placeholder="Ej: 123456789">
                            @error('numero_operacion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de depósito <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_deposito" value="{{ old('fecha_deposito') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_deposito') border-red-500 @enderror">
                            @error('fecha_deposito')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto del comprobante <span class="text-red-500">*</span></label>
                            <input type="file" name="archivo" accept="image/*,application/pdf" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('archivo') border-red-500 @enderror">
                            <p class="mt-1 text-sm text-gray-500">Formatos: JPG, PNG, PDF (máx. 40MB)</p>
                            @error('archivo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Campos para depósito en caja -->
                <div id="deposito-caja-fields" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de devolución <span class="text-red-500">*</span></label>
                            <input type="date" name="fecha_devolucion" value="{{ old('fecha_devolucion') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha_devolucion') border-red-500 @enderror">
                            @error('fecha_devolucion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <textarea name="observaciones" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('observaciones') border-red-500 @enderror"
                                placeholder="Ej: Entregado a contabilidad">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Campos comunes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Moneda <span class="text-red-500">*</span></label>
                        <select name="moneda" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('moneda') border-red-500 @enderror">
                            <option value="soles" {{ old('moneda', $anticipo->moneda) == 'soles' ? 'selected' : '' }}>Soles (S/.)</option>
                            <option value="dolares" {{ old('moneda', $anticipo->moneda) == 'dolares' ? 'selected' : '' }}>Dólares ($)</option>
                            <option value="euros" {{ old('moneda', $anticipo->moneda) == 'euros' ? 'selected' : '' }}>Euros (€)</option>
                        </select>
                        @error('moneda')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Importe <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="importe" value="{{ old('importe') }}" required min="0.01" max="{{ $saldoFinal }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('importe') border-red-500 @enderror"
                            placeholder="0.00">
                        <p class="mt-1 text-sm text-gray-500">Máximo disponible: {{ $simbolo }} {{ number_format($saldoFinal, 2) }}</p>
                        @error('importe')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('anticipos.show', $anticipo->id) }}"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>Registrar {{ ucfirst($tipo) }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMetodoPago() {
            const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
            const depositoCuentaFields = document.getElementById('deposito-cuenta-fields');
            const depositoCajaFields = document.getElementById('deposito-caja-fields');
            const bancoSelect = document.getElementById('banco-select');
            const billeteraSelect = document.getElementById('billetera-select');
            const numeroOperacion = document.querySelector('input[name="numero_operacion"]');
            const fechaDeposito = document.querySelector('input[name="fecha_deposito"]');
            const archivo = document.querySelector('input[name="archivo"]');
            const fechaDevolucion = document.querySelector('input[name="fecha_devolucion"]');

            if (metodoPago === 'deposito_cuenta') {
                depositoCuentaFields.style.display = 'block';
                depositoCajaFields.style.display = 'none';
                
                // Hacer campos requeridos
                if (bancoSelect) bancoSelect.required = false; // No requerido porque puede ser billetera
                if (billeteraSelect) billeteraSelect.required = false; // No requerido porque puede ser banco
                if (numeroOperacion) numeroOperacion.required = true;
                if (fechaDeposito) fechaDeposito.required = true;
                if (archivo) archivo.required = true;
                if (fechaDevolucion) fechaDevolucion.required = false;
            } else {
                depositoCuentaFields.style.display = 'none';
                depositoCajaFields.style.display = 'block';
                
                // Hacer campos requeridos
                if (bancoSelect) bancoSelect.required = false;
                if (billeteraSelect) billeteraSelect.required = false;
                if (numeroOperacion) numeroOperacion.required = false;
                if (fechaDeposito) fechaDeposito.required = false;
                if (archivo) archivo.required = false;
                if (fechaDevolucion) fechaDevolucion.required = true;
            }
        }

        // Ejecutar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            toggleMetodoPago();
        });
    </script>
@endsection

