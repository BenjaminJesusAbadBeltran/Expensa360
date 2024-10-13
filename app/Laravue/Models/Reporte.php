<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';
    protected $primaryKey = 'idReporte';

    protected $fillable = [
        'idGenerador',
        'tipo_reporte',
        'datos_generados',
        'idStatus'
    ];

    public function generador()
    {
        return $this->belongsTo(User::class, 'idGenerador', 'idUsuario');
    }
}
