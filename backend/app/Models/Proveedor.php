<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    // ✅ AGREGAR ESTA LÍNEA para especificar el nombre de la tabla
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'activo',
        'tiempo_entrega_dias'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con productos
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    // Relación con movimientos de inventario
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Scope para proveedores activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Método para contar productos del proveedor
    public function contarProductos()
    {
        return $this->productos()->count();
    }
}