<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | CorpSAF Comprobantes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">CorpSAF</h1>
                        <p class="text-xs text-gray-500">Comprobantes</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('comprobantes.index') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('comprobantes.index') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-th-large w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                @if(auth()->user()->isOperador())
                <a href="{{ route('comprobantes.create') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('comprobantes.create') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-plus-circle w-5"></i>
                    <span class="font-medium">Nuevo Comprobante</span>
                </a>
                @endif
                
                @if(auth()->user()->isAdmin())
                <div class="pt-2 mt-2 border-t border-gray-200">
                    <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración</p>
                    <a href="{{ route('areas.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('areas.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-building w-5"></i>
                        <span class="font-medium">Áreas/Empresas</span>
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Profile -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">DNI: {{ Auth::user()->dni ?? 'N/A' }}</p>
                        @if(Auth::user()->email)
                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        @endif
                    </div>
                </div>
                <div class="mb-2">
                    @if(Auth::user()->isAdmin())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-shield-alt mr-1"></i>Super Admin
                        </span>
                    @elseif(Auth::user()->isAreaAdmin())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-user-shield mr-1"></i>Admin Área
                        </span>
                        @if(Auth::user()->area)
                        <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->area->nombre }}</p>
                        @endif
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user mr-1"></i>Usuario / Trabajador
                        </span>
                        @if(Auth::user()->area)
                        <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->area->nombre }}</p>
                        @endif
                    @endif
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="flex items-center space-x-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                        <i class="fas fa-sign-out-alt w-4"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">@yield('title', 'Dashboard')</h2>
                        <p class="text-sm text-gray-500 mt-1">@yield('subtitle', 'Gestiona tus comprobantes')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong>Errores encontrados:</strong>
                        </div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
