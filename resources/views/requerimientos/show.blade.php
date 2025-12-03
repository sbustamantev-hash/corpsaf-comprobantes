@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8 h-screen flex flex-col">
        <!-- Header del Requerimiento -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $requerimiento->titulo }}</h1>
                    <p class="text-gray-600">
                        <span class="font-semibold">Empresa:</span> {{ $requerimiento->area->nombre ?? 'N/A' }} |
                        <span class="font-semibold">Estado:</span>
                        <span
                            class="inline-block px-2 py-1 text-xs font-semibold leading-tight rounded-full
                            {{ $requerimiento->estado == 'completado' ? 'bg-green-200 text-green-900' : ($requerimiento->estado == 'en_proceso' ? 'bg-orange-200 text-orange-900' : 'bg-red-200 text-red-900') }}">
                            {{ ucfirst(str_replace('_', ' ', $requerimiento->estado)) }}
                        </span>
                    </p>
                </div>

                @if(Auth::user()->isMarketingAdmin())
                    <div class="flex items-center space-x-4">
                        <form action="{{ route('requerimientos.progreso', $requerimiento) }}" method="POST"
                            class="flex items-center">
                            @csrf
                            <label for="porcentaje_avance" class="mr-2 text-sm font-bold text-gray-700">Avance:</label>
                            <input type="number" name="porcentaje_avance" value="{{ $requerimiento->porcentaje_avance }}"
                                min="0" max="100"
                                class="shadow appearance-none border rounded w-20 py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <span class="ml-1 text-gray-700">%</span>
                            <button type="submit"
                                class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Actualizar
                            </button>
                        </form>
                    </div>
                @else
                    <div class="w-1/3">
                        <div class="flex justify-between mb-1">
                            <span class="text-base font-medium text-blue-700 dark:text-white">Progreso</span>
                            <span
                                class="text-sm font-medium text-blue-700 dark:text-white">{{ $requerimiento->porcentaje_avance }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $requerimiento->porcentaje_avance }}%">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-grow bg-white shadow-md rounded-lg overflow-hidden flex flex-col">
            <!-- Mensajes -->
            <div class="flex-grow p-6 overflow-y-auto bg-gray-50" id="chat-messages">
                @foreach($requerimiento->mensajes as $mensaje)
                    <div class="flex mb-4 {{ $mensaje->user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div
                            class="max-w-lg {{ $mensaje->user_id === Auth::id() ? 'bg-blue-100' : 'bg-white' }} rounded-lg shadow px-4 py-2">
                            <div class="flex items-center mb-1">
                                <span
                                    class="font-bold text-sm {{ $mensaje->user_id === Auth::id() ? 'text-blue-800' : 'text-gray-800' }}">
                                    {{ $mensaje->user->name }}
                                </span>
                                <span class="text-xs text-gray-500 ml-2">{{ $mensaje->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            @if($mensaje->mensaje)
                                <p class="text-gray-800 whitespace-pre-wrap">{{ $mensaje->mensaje }}</p>
                            @endif

                            @if($mensaje->archivos->count() > 0)
                                <div class="mt-2 border-t pt-2">
                                    @foreach($mensaje->archivos as $archivo)
                                        <a href="{{ Storage::url($archivo->ruta) }}" target="_blank"
                                            class="flex items-center text-blue-600 hover:text-blue-800 mb-1">
                                            <i class="fas fa-paperclip mr-2"></i>
                                            <span class="text-sm truncate">{{ $archivo->nombre_original }}</span>
                                        </a>
                                        @if(str_contains($archivo->tipo_mime, 'image'))
                                            <img src="{{ Storage::url($archivo->ruta) }}" alt="Imagen adjunta"
                                                class="mt-2 max-w-xs rounded shadow-sm">
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-gray-100 border-t border-gray-200">
                <form action="{{ route('requerimientos.mensajes.store', $requerimiento) }}" method="POST"
                    enctype="multipart/form-data" class="flex flex-col space-y-2">
                    @csrf
                    <div class="flex items-end space-x-2">
                        <div class="flex-grow">
                            <textarea name="mensaje" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 p-2"
                                placeholder="Escribe un mensaje..."></textarea>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <label
                                class="cursor-pointer bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center justify-center transition duration-200">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" name="archivo" class="hidden">
                            </label>
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of chat
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    </script>
@endsection