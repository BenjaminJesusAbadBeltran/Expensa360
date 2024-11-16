<?php

use Illuminate\Database\Eloquent\Model;

namespace App\Laravue\Models;


class DetallePago extends Model
{
    protected $table = 'detalle_pagos';
    protected $primaryKey = 'idDetallePago';

    protected $fillable = [
        'idPago',
        'idExpensa',
        'idServicio',
        'concepto',
        'monto',
        'mes',
        'status'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'idPago', 'idPago');
    }

    public function expensa()
    {
        return $this->belongsTo(Expensa::class, 'idExpensa', 'idExpensa');
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioAgua::class, 'idServicio', 'idServicio');
    }
}