<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComprobanteController;

/*
 RUTA PRINCIPAL
*/
Route::get('/', function () {
    return redirect()->route('comprobantes.index');
});

/* 
RUTAS CRUD DE COMPROBANTES 
*/
Route::resource('comprobantes', ComprobanteController::class);
