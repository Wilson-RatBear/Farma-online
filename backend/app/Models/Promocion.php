<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    // ✅ ESTA LÍNEA ES CRUCIAL
    protected $table = 'promociones';

    protected $fillable = [
        'nombre',
        'descripcion', 
        'descuento_porcentaje',
        'imagen',
        'activo',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    public function scopeActivas($query)
    {
        return $query->where('activo', true)
                    ->whereDate('fecha_inicio', '<=', now())
                    ->whereDate('fecha_fin', '>=', now());
    }
}