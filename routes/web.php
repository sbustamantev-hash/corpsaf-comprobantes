<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\Auth\LoginController;

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
    if (auth()->check()) {
        return redirect()->route('comprobantes.index');
    }
    return redirect()->route('login');
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
});
