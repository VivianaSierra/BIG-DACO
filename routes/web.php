<?php

use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('inicio');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::post('/chatbot', \App\Http\Controllers\ChatbotController::class)
        ->middleware('throttle:20,1')
        ->name('chatbot');

    Route::resource('productos', ProductoController::class)->except(['show']);

  
    Route::view('/proveedores','proveedores.index');
    Route::view('/proveedores/create','proveedores.create');

  
    Route::resource('ventas', VentaController::class)->except(['show', 'edit', 'update']);
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');

  
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

 
    Route::view('/reportes/ventas','reportes.ventas');

});


require __DIR__.'/auth.php';