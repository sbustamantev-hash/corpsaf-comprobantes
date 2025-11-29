<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Sistema | CorpSAF</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 50px, rgba(255, 255, 255, .03) 50px, rgba(255, 255, 255, .03) 51px),
                repeating-linear-gradient(90deg, transparent, transparent 50px, rgba(255, 255, 255, .03) 50px, rgba(255, 255, 255, .03) 51px);
            background-size: 100px 100px;
            pointer-events: none;
            z-index: 0;
        }

        body>* {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4"
    style="background-image: url('{{ asset('img/background_sistemas.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="w-full max-w-4xl z-10">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-2xl mb-6 shadow-lg">
                @if(isset($logoPath) && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" class="w-16 h-16 object-contain">
                @else
                    <i class="fas fa-cubes text-white text-4xl"></i>
                @endif
            </div>
            <h1 class="text-4xl font-bold text-white mb-3 drop-shadow-lg">Bienvenido, {{ Auth::user()->name }}</h1>
            <p class="text-xl text-white drop-shadow">Selecciona el sistema que deseas utilizar</p>
        </div>

        <!-- Mensajes -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-center">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Grid de Sistemas -->
        <div class="flex justify-center items-center">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($sistemas as $sistema)
                    <form action="{{ route('sistemas.seleccionar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="sistema_id" value="{{ $sistema['id'] }}">
                        <button type="submit"
                            class="w-64 h-64 bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 p-6 flex flex-col items-center justify-center border-2 border-transparent hover:border-blue-500 group">
                            <!-- Icono -->
                            <div class="mb-4">
                                @php
                                    $colorMap = [
                                        'blue' => ['from-blue-500', 'to-blue-600'],
                                        'green' => ['from-green-500', 'to-green-600'],
                                        'purple' => ['from-purple-500', 'to-purple-600'],
                                        'red' => ['from-red-500', 'to-red-600'],
                                        'yellow' => ['from-yellow-500', 'to-yellow-600'],
                                        'indigo' => ['from-indigo-500', 'to-indigo-600'],
                                    ];
                                    $gradient = $colorMap[$sistema['color']] ?? $colorMap['blue'];
                                @endphp
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br {{ $gradient[0] }} {{ $gradient[1] }} rounded-xl shadow-md group-hover:scale-110 transition-transform duration-300">
                                    @if(isset($logoPath) && $logoPath)
                                        <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo"
                                            class="w-12 h-12 object-contain">
                                    @else
                                        <i class="fas {{ $sistema['icono'] }} text-white text-2xl"></i>
                                    @endif
                                </div>
                            </div>

                            <!-- Título y Subtítulo -->
                            @php
                                $textColorMap = [
                                    'blue' => 'text-blue-600',
                                    'green' => 'text-green-600',
                                    'purple' => 'text-purple-600',
                                    'red' => 'text-red-600',
                                    'yellow' => 'text-yellow-600',
                                    'indigo' => 'text-indigo-600',
                                ];
                                $textColor = $textColorMap[$sistema['color']] ?? 'text-blue-600';
                                $hoverColor = str_replace('600', '700', $textColor);
                            @endphp
                            <h3
                                class="text-lg font-bold text-gray-900 mb-1 text-center group-hover:{{ $textColor }} transition-colors">
                                {{ $sistema['nombre'] }}
                            </h3>
                            <p class="text-sm font-semibold {{ $textColor }} mb-3 text-center">
                                {{ $sistema['subtitulo'] }}
                            </p>

                            <!-- Descripción -->
                            <p class="text-xs text-gray-600 mb-4 text-center flex-grow">
                                {{ Str::limit($sistema['descripcion'], 60) }}
                            </p>

                            <!-- Botón de acción -->
                            <div class="mt-auto">
                                <div
                                    class="inline-flex items-center {{ $textColor }} font-semibold group-hover:{{ $hoverColor }}">
                                    <span class="text-sm">Ingresar</span>
                                    <i
                                        class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-2 transition-transform"></i>
                                </div>
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</body>

</html>