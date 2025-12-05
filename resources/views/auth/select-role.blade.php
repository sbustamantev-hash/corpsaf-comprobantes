<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Rol | {{ $nombreApp ?? 'YnnovaCorp' }} Comprobantes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body
    class="bg-gradient-to-br from-blue-500 via-blue-600 to-purple-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-xl mb-4">
                @if(isset($logoPath) && $logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo"
                        class="w-full h-full object-contain rounded-xl">
                @else
                    <i class="fas fa-user-shield text-white text-3xl"></i>
                @endif
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $nombreApp ?? 'YnnovaCorp' }}</h1>
            <p class="text-gray-600">Selecciona cómo deseas ingresar</p>
            <p class="text-sm text-gray-500 mt-2">Este DNI tiene múltiples roles disponibles</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="dni" value="{{ $dni }}">
            <input type="hidden" name="password" value="{{ $password }}">
            @if($remember)
                <input type="hidden" name="remember" value="1">
            @endif

            <div class="space-y-4">
                @foreach($users as $user)
                    <button type="submit" name="role" value="{{ $user->role }}"
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition shadow-lg flex items-center justify-center space-x-3">
                        <i class="fas 
                            @if($user->isAdmin()) fa-user-shield
                            @elseif($user->isAreaAdmin()) fa-user-tie
                            @elseif($user->isMarketingAdmin()) fa-bullhorn
                            @else fa-user
                            @endif
                            text-xl"></i>
                        <div class="text-left">
                            <div class="font-bold">{{ $user->role_label }}</div>
                            @if($user->area)
                                <div class="text-sm opacity-90">{{ $user->area->nombre }}</div>
                            @endif
                        </div>
                        <i class="fas fa-arrow-right ml-auto"></i>
                    </button>
                @endforeach
            </div>

            <div class="pt-4 border-t border-gray-200">
                <a href="{{ route('login') }}"
                    class="block text-center text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Volver al inicio de sesión
                </a>
            </div>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-xs text-gray-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    Selecciona el rol con el que deseas acceder al sistema
                </p>
            </div>
        </div>
    </div>
</body>

</html>

