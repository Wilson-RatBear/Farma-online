<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    // ✅ AGREGAR ESTA LÍNEA - DESHABILITAR TIMESTAMPS AUTOMÁTICOS
    public $timestamps = false;

    protected $fillable = [
        'pedido_id',
        'metodo_pago',
        'referencia',
        'banco',
        'telefono',
        'monto',
        'estado'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}