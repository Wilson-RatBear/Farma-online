<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'precio_original', 
        'stock',
        'imagen',
        'categoria_id',
        'badge',
        'activo'
    ];

    // Conversión de tipos
    protected $casts = [
        'precio' => 'decimal:2',
        'precio_original' => 'decimal:2',
        'activo' => 'boolean'
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}