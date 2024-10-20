<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';

    protected $primaryKey = 'idEgreso';

    protected $fillable = [
        'idCajaChica',
        'concepto',
        'monto',
        'fechaEgreso',
        'status',
    ];

    public function cajaChica()
    {
        return $this->belongsTo(CajaChica::class, 'idCajaChica', 'idCajaChica');
    }
}
