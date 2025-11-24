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

                {{-- Tipo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de comprobante</label>
                    <input type="text" 
                           name="tipo" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tipo') border-red-500 @enderror"
                           value="{{ old('tipo', $comprobante->tipo) }}"
                           required>
                    @error('tipo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha y Monto --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del comprobante</label>
                        <input type="date" 
                               name="fecha" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('fecha') border-red-500 @enderror"
                               value="{{ old('fecha', $comprobante->fecha) }}"
                               required>
                        @error('fecha')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto (S/.)</label>
                        <input type="number" 
                               name="monto" 
                               step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monto') border-red-500 @enderror"
                               value="{{ old('monto', $comprobante->monto) }}"
                               required>
                        @error('monto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Detalle --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Detalle (opcional)</label>
                    <textarea name="detalle" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('detalle') border-red-500 @enderror">{{ old('detalle', $comprobante->detalle) }}</textarea>
                    @error('detalle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="archivo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                    <span>Subir nuevo archivo</span>
                                    <input id="archivo" name="archivo" type="file" class="sr-only" accept="image/*,application/pdf">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 2MB</p>
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
@endsection
