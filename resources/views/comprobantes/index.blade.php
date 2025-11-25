@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', Auth::user()->isOperador() ? 'Gestiona tus anticipos y reembolsos' : 'Resumen de tus comprobantes')

@section('header-actions')
    @if(Auth::user()->isOperador())
        {{-- Sin acciones en el header para operadores --}}
    @endif
@endsection

@section('content')
    @php
        $user = Auth::user();
        $anticipos = isset($anticipos) ? $anticipos : collect();
        
        if ($user->isOperador()) {
            // Métricas para operadores: basadas en anticipos/reembolsos
            $anticiposPendientes = $anticipos->where('estado', '!=', 'completo')->count();
            $anticiposCompletos = $anticipos->where('estado', 'completo')->count();
            $totalAnticipos = $anticipos->count();
            $totalAsignado = $anticipos->sum('importe');
            $totalComprobado = $anticipos->sum(function($a) { return $a->comprobantes->sum('monto'); });
        } else {
            // Métricas para admin: basadas en comprobantes
            if ($user->isAdmin()) {
                $pendientes = $comprobantes->where('estado', 'pendiente')->count();
                $aprobados = $comprobantes->where('estado', 'aprobado')->count();
                $rechazados = $comprobantes->where('estado', 'rechazado')->count();
            } else {
                $pendientes = $comprobantes->where('estado', 'pendiente')->count();
                $aprobados = $comprobantes->where('estado', 'aprobado')->count();
                $rechazados = $comprobantes->where('estado', 'rechazado')->count();
            }
            $total = $comprobantes->count();
        }
    @endphp

    @if(Auth::user()->isOperador())
    <!-- Summary Cards para Operadores -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $anticiposPendientes }}</p>
                    <p class="text-xs text-gray-500 mt-1">Anticipos/Reembolsos</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Completados</p>
                    <p class="text-3xl font-bold text-green-600">{{ $anticiposCompletos }}</p>
                    <p class="text-xs text-gray-500 mt-1">Anticipos/Reembolsos</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Asignado</p>
                    <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($totalAsignado, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Monto recibido</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Comprobado</p>
                    <p class="text-2xl font-bold text-purple-600">S/ {{ number_format($totalComprobado, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Con comprobantes</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Summary Cards para Admin/Area Admin -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pendientes</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $pendientes }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Aprobados</p>
                    <p class="text-3xl font-bold text-green-600">{{ $aprobados }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Rechazados</p>
                    <p class="text-3xl font-bold text-red-600">{{ $rechazados }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $total }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(Auth::user()->isAreaAdmin())
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Usuarios de mi Área</h3>
                <p class="text-sm text-gray-500">Crea anticipos o reembolsos para tus colaboradores</p>
            </div>
        </div>
        @if($operadores->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($operadores as $operador)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $operador->name }}
                                    <p class="text-xs text-gray-500">Usuario / Trabajador</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $operador->dni ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $operador->email ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="w-full">
                                        <a href="{{ route('areas.users.anticipos.create', [Auth::user()->area_id, $operador->id]) }}"
                                           class="w-full flex justify-center items-center px-4 py-2 text-sm font-semibold bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
                                            <i class="fas fa-money-bill-wave mr-2"></i>Anticipo/Reembolso
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-gray-500 text-center">
                No hay usuarios cargados en tu área todavía.
            </div>
        @endif
    </div>
    @endif

    @if(Auth::user()->isOperador())
    <!-- Anticipos / Reembolsos Asignados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Mis Anticipos y Reembolsos</h3>
                <p class="text-sm text-gray-500 mt-1">Sube los comprobantes que justifican el uso del dinero recibido</p>
            </div>
        </div>
        @if($anticipos->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Asignación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe Recibido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($anticipos as $anticipo)
                            @php
                                $totalComprobado = $anticipo->comprobantes->sum('monto');
                                $porcentaje = $anticipo->importe > 0 ? min(100, ($totalComprobado / $anticipo->importe) * 100) : 0;
                                $restante = max(0, $anticipo->importe - $totalComprobado);
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($anticipo->tipo === 'anticipo')
                                            <i class="fas fa-arrow-down text-green-600 mr-2"></i>
                                        @else
                                            <i class="fas fa-arrow-up text-blue-600 mr-2"></i>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900 capitalize">
                                            {{ $anticipo->tipo === 'anticipo' ? 'Anticipo' : 'Reembolso' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $anticipo->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">S/ {{ number_format($anticipo->importe, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-700">S/ {{ number_format($totalComprobado, 2) }}</span>
                                    @if($restante > 0)
                                        <p class="text-xs text-gray-500 mt-1">Falta: S/ {{ number_format($restante, 2) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2" style="min-width: 80px;">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ number_format($porcentaje, 0) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($anticipo->estado === 'completo')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Completo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('comprobantes.create', ['anticipo_id' => $anticipo->id]) }}"
                                       class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium flex items-center justify-center space-x-2">
                                        <i class="fas fa-file-upload"></i>
                                        <span>Subir Comprobante</span>
                                    </a>
                                </td>
                            </tr>
                            @if($anticipo->comprobantes->count() > 0)
                            <tr id="comprobantes-{{ $anticipo->id }}" class="bg-gray-50">
                                <td colspan="7" class="px-6 py-4">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>
                                            Comprobantes subidos ({{ $anticipo->comprobantes->count() }})
                                        </h4>
                                        <div class="space-y-2">
                                            @foreach($anticipo->comprobantes as $comprobante)
                                                <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200">
                                                    <div class="flex items-center space-x-3">
                                                        <i class="fas fa-file-pdf text-red-600"></i>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ $comprobante->tipo }}</p>
                                                            <p class="text-xs text-gray-500">{{ $comprobante->fecha }} - S/ {{ number_format($comprobante->monto, 2) }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        @if($comprobante->archivo)
                                                            <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                                                               target="_blank"
                                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                                <i class="fas fa-eye mr-1"></i>Ver
                                                            </a>
                                                        @endif
                                                        @if($comprobante->estado === 'aprobado')
                                                            <span class="text-xs text-green-600 font-medium">
                                                                <i class="fas fa-check-circle mr-1"></i>Aprobado
                                                            </span>
                                                        @elseif($comprobante->estado === 'rechazado')
                                                            <span class="text-xs text-red-600 font-medium">
                                                                <i class="fas fa-times-circle mr-1"></i>Rechazado
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-yellow-600 font-medium">
                                                                <i class="fas fa-clock mr-1"></i>Pendiente
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg font-medium mb-2">No tienes anticipos o reembolsos asignados</p>
                <p class="text-gray-400 text-sm">Cuando tu administrador te asigne un anticipo o reembolso, aparecerá aquí para que puedas subir los comprobantes correspondientes.</p>
            </div>
        @endif
    </div>
    @endif

    @if(Auth::user()->isAreaAdmin())
    <!-- Anticipos y Reembolsos Realizados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Anticipos y Reembolsos Realizados</h3>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Buscar anticipos..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($anticipos->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay anticipos o reembolsos registrados aún</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprobado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($anticipos as $anticipo)
                            @php
                                $totalComprobado = $anticipo->comprobantes->sum('monto');
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $anticipo->usuario->name ?? '-' }}
                                    <p class="text-xs text-gray-500">{{ $anticipo->usuario->dni ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">
                                    {{ $anticipo->tipo }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $anticipo->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    S/ {{ number_format($anticipo->importe, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    S/ {{ number_format($totalComprobado, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($anticipo->estado === 'completo')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Completo
                                        </span>
                                    @elseif($anticipo->estado === 'aprobado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-check-circle mr-1"></i>Aprobado
                                        </span>
                                    @elseif($anticipo->estado === 'rechazado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rechazado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-hourglass-half mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('anticipos.show', $anticipo->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded transition"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif

    @if(!Auth::user()->isOperador())
    <!-- Comprobantes Table (solo para Admin y Area Admin) -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Comprobantes Registrados</h3>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Buscar comprobantes..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($comprobantes->isEmpty())
            <div class="p-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No hay comprobantes registrados aún</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                @endif
                            @endauth
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($comprobantes as $comprobante)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $comprobante->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->tipo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->fecha }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">S/ {{ number_format($comprobante->monto, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $comprobante->detalle ?? '-' }}</td>
                                @auth
                                    @if(Auth::user()->isAdmin())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $comprobante->user->name ?? '-' }}</td>
                                    @endif
                                @endauth
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($comprobante->estado === 'aprobado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Aprobado
                                        </span>
                                    @elseif($comprobante->estado === 'rechazado')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Rechazado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($comprobante->archivo)
                                        <a href="{{ route('comprobantes.download', $comprobante->id) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                            <i class="fas fa-file mr-1"></i>Ver
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Sin archivo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('comprobantes.show', $comprobante->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded transition">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @auth
                                            @if(!Auth::user()->isAdmin())
                                                <a href="{{ route('comprobantes.edit', $comprobante->id) }}" 
                                                   class="text-yellow-600 hover:text-yellow-900 p-2 hover:bg-yellow-50 rounded transition">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        @endauth
                                        @auth
                                            @if(!Auth::user()->isAdmin() && $comprobante->user_id === Auth::id())
                                                <form action="{{ route('comprobantes.destroy', $comprobante->id) }}" 
                                                      method="POST"
                                                      class="inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded transition">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif
@endsection

