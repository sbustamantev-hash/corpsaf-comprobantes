<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | YnnovaCorp Comprobantes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body
    class="bg-gradient-to-br from-blue-500 via-blue-600 to-purple-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-xl mb-4">
                <i class="fas fa-file-invoice-dollar text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">YnnovaCorp</h1>
            <p class="text-gray-600">Liquidacion de gastos</p>
            <p class="text-sm text-gray-500 mt-2">Inicia sesión para continuar</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>Error de autenticación</strong>
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-2 text-gray-400"></i>DNI
                </label>
                <input type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dni') border-red-500 @enderror"
                    id="dni" name="dni" value="{{ old('dni') }}" required autofocus placeholder="Ingresa tu DNI">
                @error('dni')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-gray-400"></i>Contraseña
                </label>
                <input type="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                    id="password" name="password" required placeholder="Ingresa tu contraseña">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    id="remember" name="remember">
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                    Recordarme
                </label>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition shadow-lg">
                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-xs text-gray-600 text-center">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Accesos:</strong><br>
                    Usa tu DNI como usuario y contraseña. <strong>Recuerda que la contraseña es intransferible</strong>
                    Bienvenido
                </p>
            </div>
        </div>
    </div>
</body>

</html>