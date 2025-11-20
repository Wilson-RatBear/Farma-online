<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'producto_id', 
        'cantidad',
        'precio_unitario'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2'
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}