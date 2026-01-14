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
        'activo',
        'stock_minimo',
        'stock_maximo', 
        'proveedor_id',
        'costo',
        'alertar_stock_bajo',
        'ultima_reposicion'
    ];

    // Conversión de tipos
    protected $casts = [
        'precio' => 'decimal:2',
        'precio_original' => 'decimal:2',
        'activo' => 'boolean',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'costo' => 'decimal:2',
        'alertar_stock_bajo' => 'boolean',
        'ultima_reposicion' => 'datetime'
    ];

    // Relación con categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // Relación con movimientos de inventario
    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Verificar si el stock está bajo
    public function stockBajo()
    {
        return $this->stock < $this->stock_minimo;
    }

    // Verificar si el stock está sobre el máximo
    public function stockSobreMaximo()
    {
        return $this->stock > $this->stock_maximo;
    }

    // Calcular días desde última reposición
    public function diasDesdeUltimaReposicion()
    {
        if (!$this->ultima_reposicion) {
            return null;
        }
        return $this->ultima_reposicion->diffInDays(now());
    }

    // Scope para productos con stock bajo
    public function scopeStockBajo($query)
    {
        return $query->where('alertar_stock_bajo', true)
                    ->whereColumn('stock', '<', 'stock_minimo')
                    ->where('activo', true);
    }

    // Scope para productos que necesitan reposición
    public function scopeNecesitanReposicion($query)
    {
        return $query->where('alertar_stock_bajo', true)
                    ->whereColumn('stock', '<', 'stock_minimo')
                    ->where('activo', true);
    }
}