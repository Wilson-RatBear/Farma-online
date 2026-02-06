<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'numero_orden', 
        'total',
        'estado',
        'direccion_envio',
        'ciudad_envio',
        'telefono_contacto',
        'metodo_pago',
        'metodo_pago_detalle',
        'referencia_pago',
        'banco',
        'telefono_pago',
        'monto_pagado',
        'estado_pago',
        'fecha_pago',
        'notas'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con los items del pedido
    public function items()
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    // Generar número de orden único
    public static function generarNumeroOrden()
    {
        $prefix = 'ORD';
        $fecha = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return $prefix . $fecha . $random;
    }
}