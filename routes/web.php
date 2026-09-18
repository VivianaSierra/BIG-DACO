<?php

use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    return view('inicio');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::resource('productos', ProductoController::class)->except(['show']);


    Route::view('/proveedores','proveedores.index');
    Route::view('/proveedores/create','proveedores.create');


    Route::resource('ventas', VentaController::class)->except(['show', 'edit', 'update']);
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');


    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

    Route::get('/reportes/ventas', [ReporteController::class, 'index'])->name('reportes.ventas');
    Route::get('/reportes/ventas/exportar', [ReporteController::class, 'export'])->name('reportes.ventas.exportar');

});

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
