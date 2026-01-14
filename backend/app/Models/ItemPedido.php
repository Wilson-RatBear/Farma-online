<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'items_pedido';
    
    // Indicar que no usa timestamps automáticos
    public $timestamps = false;

    protected $fillable = [
        'pedido_id',
        'producto_id', 
        'cantidad',
        'precio_unitario'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    // Relación con el pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Calcular subtotal (atributo computado)
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}