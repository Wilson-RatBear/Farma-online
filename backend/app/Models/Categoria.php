<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    // La tabla se llama 'categorias' (ya coincide por convención)
    // protected $table = 'categorias';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
        'slug', 
        'descripcion'
    ];

    // Relación con productos
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}