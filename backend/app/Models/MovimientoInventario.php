<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'observaciones',
        'usuario_id',
        'proveedor_id'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
    ];

    // Relación con producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Scope para movimientos de entrada
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    // Scope para movimientos de salida
    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    // Scope por rango de fechas
    public function scopeRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }
}