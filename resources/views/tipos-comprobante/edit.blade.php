@extends('layouts.app')

@section('title', 'Editar Tipo de Comprobante')
@section('subtitle', 'Modificar datos del tipo de comprobante')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('tipos-comprobante.update', $tipoComprobante->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código *</label>
                    <input type="text" 
                           name="codigo" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('codigo') border-red-500 @enderror" 
                           value="{{ old('codigo', $tipoComprobante->codigo) }}" 
                           required>
                    @error('codigo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                    <input type="text" 
                           name="descripcion" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descripcion') border-red-500 @enderror" 
                           value="{{ old('descripcion', $tipoComprobante->descripcion) }}" 
                           required>
                    @error('descripcion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="activo" 
                           id="activo"
                           value="1"
                           {{ old('activo', $tipoComprobante->activo) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="activo" class="ml-2 block text-sm text-gray-700">
                        Tipo activo
                    </label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-4">
                <a href="{{ route('tipos-comprobante.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-save mr-2"></i>Actualizar Tipo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

