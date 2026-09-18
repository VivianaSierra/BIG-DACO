<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
'nombre',
'categoria',
'precio',
'stock'
];

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }
}
