<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $primaryKey = 'idMetodo';

    protected $fillable = [
        'nombre',
        'cuenta',
        'idStatus',
    ];
}
