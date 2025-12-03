@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Nuevo Requerimiento a Marketing</h2>
            </div>

            <form action="{{ route('requerimientos.store') }}" method="POST" class="p-6">
                @csrf

                <div class="mb-4">
                    <label for="titulo" class="block text-gray-700 text-sm font-bold mb-2">Título del Requerimiento</label>
                    <input type="text" name="titulo" id="titulo"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        required placeholder="Ej: Diseño de banner para redes sociales">
                </div>

                <div class="mb-6">
                    <label for="detalle" class="block text-gray-700 text-sm font-bold mb-2">Detalle Inicial
                        (Mensaje)</label>
                    <textarea name="detalle" id="detalle" rows="4"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Describe brevemente lo que necesitas..."></textarea>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('requerimientos.index') }}" class="text-gray-600 hover:text-gray-800 font-bold">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Crear Requerimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection