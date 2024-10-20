<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Laravue\Models\UsuarioServicio;

class ServicioAgua extends Model
{
    use HasFactory;

    protected $table = 'servicios_agua';

    protected $primaryKey = 'idServicio';

    protected $fillable = [
        'idPropiedad',
        'montoPagar',
        'fechaMedicion',
        'medicion',
        'previaMedicion',
        'status'
    ];

    protected $casts = [
        'montoPagar' => 'decimal:2',
        'fechaMedicion' => 'date',
        'medicion' => 'integer',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'idPropiedad', 'idPropiedad');
    }
}
