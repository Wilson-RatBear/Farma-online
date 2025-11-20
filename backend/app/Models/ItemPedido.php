<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    // CORREGIR: La tabla se llama 'items_pedido' (con S)
    protected $table = 'items_pedido';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'pedido_id',
        'producto_id', 
        'cantidad',
        'precio_unitario'
    ];

    // Conversión de tipos
    protected $casts = [
        'precio_unitario' => 'decimal:2'
    ];

    // Relación con pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}