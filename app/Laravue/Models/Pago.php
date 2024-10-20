<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'pagos';
    protected $primaryKey = 'idPago';
    
    protected $fillable = [
        'idUsuario',
        'idPropiedad',
        'idMetodoPago',
        'montoTotal',
        'fechaPago',
        'observaciones',
        'status',
    ];

    protected $casts = [
        'montoTotal' => 'decimal:2',
        'fechaPago' => 'datetime',
    ];

    /**
     * Get the metodoPago associated with the Pago.
     */
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'idMetodoPago', 'idMetodo'); // Especifica las columnas correctas
    }

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'idPropiedad', 'idPropiedad');
    }

    public function usuarios()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }
    
}
