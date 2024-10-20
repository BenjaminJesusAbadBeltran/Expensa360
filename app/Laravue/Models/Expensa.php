<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expensa extends Model
{
    use HasFactory;

    protected $table = 'expensas';

    protected $primaryKey = 'idExpensa';

    protected $fillable = [
        'idPropiedad',
        'monto',
        'montoPagado',
        'montoPendiente',
        'montoAhorro',
        'mes',
        'estado',
        'status',
    ];

    protected $casts = [
        'monto' => 'float',
        'montoPagado' => 'float',
        'montoPendiente' => 'float',
        'montoAhorro' => 'float',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'idPropiedad', 'idPropiedad');
    }
}
