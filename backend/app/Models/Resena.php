<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    protected $table = 'resenas';

    protected $fillable = [
        'usuario_id',
        'producto_id',
        'rating',
        'comentario'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    // Relación con User
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Scope para reseñas activas (con usuario y producto activos)
    public function scopeActivas($query)
    {
        return $query->whereHas('usuario')
                    ->whereHas('producto', function($q) {
                        $q->where('activo', true);
                    });
    }
}