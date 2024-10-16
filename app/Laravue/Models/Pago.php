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
        'idMetodoPago',
        'idCajaChica',
        'monto',
        'fechaPago',
        'idStatus',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fechaPago' => 'datetime',
        'idStatus' => 'integer',
    ];

    /**
     * Get the metodoPago associated with the Pago.
     */
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'idMetodoPago', 'idMetodo'); // Especifica las columnas correctas
    }

    /**
     * Get the cajaChica associated with the Pago.
     */
    public function cajaChica()
    {
        return $this->belongsTo(CajaChica::class, 'idCajaChica');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_pago', 'idPago', 'idUsuario')->using(UsuarioPago::class);
    }
}
