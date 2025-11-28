<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Administrador</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-50 min-h-screen">
    @php
        use App\Models\Configuracion;

        $sidebarRole = auth()->user()->isAdmin()
            ? 'Soporte'
            : (auth()->user()->isAreaAdmin() ? 'Administrador' : 'Usuario');
        $sidebarAppName = Configuracion::obtener('nombre_app', 'YnnovaCorp');
        $sidebarCompanyName = Configuracion::obtener('nombre_empresa', $sidebarAppName);
        $sidebarLogo = Configuracion::obtener('logo_path');
        $logoImage = $sidebarLogo ? asset('storage/' . $sidebarLogo) : null;
        $logoWrapperClasses = auth()->user()->isAdmin() ? 'cursor-pointer group' : '';
    @endphp

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->isAdmin())
                        <button type="button"
                                onclick="toggleLogoModal(true)"
                                class="relative w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden group focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                            @if($logoImage)
                                <img src="{{ $logoImage }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                            @endif
                            <div class="absolute inset-0 rounded-full bg-black/60 hidden group-hover:flex flex-col items-center justify-center text-white text-[10px] font-semibold transition pointer-events-none">
                                <i class="fas fa-pen mb-0.5 text-xs"></i>
                                <span>Editar</span>
                            </div>
                        </button>
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden">
                            @if($logoImage)
                                <img src="{{ $logoImage }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                            @endif
                        </div>
                    @endif
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $sidebarRole }}</h1>
                        <p class="text-xs text-gray-500">{{ $sidebarCompanyName }}</p>
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

                @if(auth()->user()->isAdmin() || auth()->user()->isAreaAdmin())
                <div class="pt-2 mt-2 border-t border-gray-200">
                    <p class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración</p>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('areas.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('areas.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-building w-5"></i>
                        <span class="font-medium">Empresas</span>
                    </a>
                    <a href="{{ route('tipos-comprobante.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('tipos-comprobante.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-file-invoice w-5"></i>
                        <span class="font-medium">Tipos de Comprobante</span>
                    </a>
                    <a href="{{ route('bancos.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('bancos.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-university w-5"></i>
                        <span class="font-medium">Bancos</span>
                    </a>
                    <a href="{{ route('conceptos.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('conceptos.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-tags w-5"></i>
                        <span class="font-medium">Conceptos</span>
                    </a>
                    @endif
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fas fa-users w-5"></i>
                        <span class="font-medium">Usuarios</span>
                    </a>
                </div>
                @endif
            </nav>

            <!-- User Profile -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-3">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
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
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-shield-alt mr-1"></i>Soporte
                        </span>
                    @elseif(Auth::user()->isAreaAdmin())
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-user-shield mr-1"></i>Admin Empresa
                        </span>
                        @if(Auth::user()->area)
                            <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->area->nombre }}</p>
                        @endif
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-user mr-1"></i>Usuario / Trabajador
                        </span>
                        @if(Auth::user()->area)
                            <p class="text-xs text-gray-500 mt-1">{{ Auth::user()->area->nombre }}</p>
                        @endif
                    @endif
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit"
                        class="flex items-center space-x-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
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
                    <div
                        class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
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
    
    @if(auth()->user()->isAdmin())
    <!-- Modal para actualizar logo -->
    <div id="logoModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Actualizar logo</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="toggleLogoModal(false)">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Selecciona una nueva imagen para el logo de la empresa. Recomendado: fondo transparente, formato PNG o SVG.</p>
            <form action="{{ route('configuraciones.branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la empresa</label>
                    <input type="text"
                           name="nombre_empresa"
                           value="{{ $sidebarCompanyName }}"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                    <input type="file" name="logo" accept="image/*"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Déjalo vacío si solo quieres actualizar el nombre.</p>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <button type="button"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                            onclick="toggleLogoModal(false)">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        Guardar logo
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</body>

@if(auth()->user()->isAdmin())
<script>
    function toggleLogoModal(show) {
        const modal = document.getElementById('logoModal');
        if (!modal) return;
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleLogoModal(false);
        }
    });
</script>
@endif

</html>