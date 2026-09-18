<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'total',
        'estado',
        'anulada_at',
    ];

    protected $casts = [
        'anulada_at' => 'datetime',
    ];

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
