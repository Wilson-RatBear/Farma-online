<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    // Estados posibles del pedido
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_ENVIADO = 'enviado';
    const ESTADO_ENTREGADO = 'entregado';
    const ESTADO_CANCELADO = 'cancelado';

    // Métodos de pago
    const METODO_TARJETA = 'tarjeta';
    const METODO_EFECTIVO = 'efectivo';

    protected $fillable = [
        'usuario_id',
        'numero_orden', 
        'total',
        'estado',
        'direccion_envio',
        'ciudad_envio',
        'telefono_contacto',
        'metodo_pago'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con items del pedido
    public function items()
    {
        return $this->hasMany(ItemPedido::class);
    }

    // Generar número de orden único
    public static function generarNumeroOrden()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}