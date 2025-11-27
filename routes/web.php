<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AnticipoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipoComprobanteController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\ConfiguracionController;

/*
 RUTAS DE AUTENTICACIÓN
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
 RUTA PRINCIPAL - Redirige a login si no está autenticado
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('sistemas.index');
    }
    return redirect()->route('login');
});

/*
 RUTAS DE SELECCIÓN DE SISTEMAS - Requieren autenticación
*/
Route::middleware('auth')->group(function () {
    Route::get('/sistemas', [SistemaController::class, 'index'])->name('sistemas.index');
    Route::post('/sistemas/seleccionar', [SistemaController::class, 'seleccionar'])->name('sistemas.seleccionar');
});

/* 
RUTAS CRUD DE COMPROBANTES - Requieren autenticación
*/
Route::middleware('auth')->group(function () {
    Route::resource('comprobantes', ComprobanteController::class);
    Route::get('comprobantes/{id}/archivo', [ComprobanteController::class, 'download'])->name('comprobantes.download');

    // Rutas para aprobar/rechazar y observaciones
    Route::post('comprobantes/{id}/aprobar', [ComprobanteController::class, 'aprobar'])->name('comprobantes.aprobar');
    Route::post('comprobantes/{id}/rechazar', [ComprobanteController::class, 'rechazar'])->name('comprobantes.rechazar');
    Route::post('comprobantes/{id}/observacion', [ComprobanteController::class, 'agregarObservacion'])->name('comprobantes.observacion');
    Route::get('observaciones/{id}/archivo', [ComprobanteController::class, 'downloadObservacion'])->name('observaciones.download');

    // RUTAS CRUD DE EmpresaS - Solo super admin
    Route::resource('areas', AreaController::class);

    // Rutas para gestionar usuarios de Empresas
    Route::post('areas/{area}/users', [AreaController::class, 'storeUser'])->name('areas.users.store');
    Route::put('areas/{area}/users/{user}', [AreaController::class, 'updateUser'])->name('areas.users.update');
    Route::delete('areas/{area}/users/{user}', [AreaController::class, 'destroyUser'])->name('areas.users.destroy');

    // RUTAS CRUD DE USUARIOS - Admin y Area Admin
    Route::resource('users', UserController::class);
    
    // RUTAS CRUD DE TIPOS DE COMPROBANTE - Solo super admin
    Route::resource('tipos-comprobante', TipoComprobanteController::class);
    
    // RUTAS CRUD DE BANCOS - Solo super admin
    Route::resource('bancos', BancoController::class);
    
    // RUTAS DE CONFIGURACIONES - Solo super admin
    Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
    Route::put('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');
    Route::post('configuraciones/branding', [ConfiguracionController::class, 'updateBranding'])->name('configuraciones.branding.update');

    // Anticipos
    Route::get('areas/{area}/users/{user}/anticipos/create', [AnticipoController::class, 'create'])->name('areas.users.anticipos.create');
    Route::post('areas/{area}/users/{user}/anticipos', [AnticipoController::class, 'store'])->name('areas.users.anticipos.store');
    Route::post('anticipos/{anticipo}/comprobantes', [AnticipoController::class, 'uploadComprobante'])->name('anticipos.comprobantes.store');

    // Rutas para ver, aprobar/rechazar y exportar anticipos
    Route::get('anticipos/{anticipo}', [AnticipoController::class, 'show'])->name('anticipos.show');
    Route::post('anticipos/{anticipo}/aprobar', [AnticipoController::class, 'aprobar'])->name('anticipos.aprobar');
    Route::post('anticipos/{anticipo}/rechazar', [AnticipoController::class, 'rechazar'])->name('anticipos.rechazar');
    Route::get('anticipos/{anticipo}/export/pdf', [AnticipoController::class, 'exportPdf'])->name('anticipos.export.pdf');
    Route::get('anticipos/{anticipo}/export/excel', [AnticipoController::class, 'exportExcel'])->name('anticipos.export.excel');
});
