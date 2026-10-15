<?php

use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::post('/chatbot', \App\Http\Controllers\ChatbotController::class)
        ->middleware('throttle:20,1')
        ->name('chatbot');

    Route::resource('productos', ProductoController::class)->except(['show']);

  
    Route::resource('proveedores', \App\Http\Controllers\ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->except(['show']);

  
    Route::resource('ventas', VentaController::class)->except(['show', 'edit', 'update']);
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');

  
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

 
    Route::get('/reportes/ventas', [\App\Http\Controllers\ReporteController::class, 'ventas'])->name('reportes.ventas');

});


require __DIR__.'/auth.php';