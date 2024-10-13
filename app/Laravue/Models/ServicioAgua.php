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
        'montoPagar',
        'fechaMedicion',
        'medicion',
        'previaMedicion',
        'idStatus'
    ];

    protected $casts = [
        'montoPagar' => 'decimal:2',
        'fechaMedicion' => 'datetime',
        'medicion' => 'integer',
        'previaMedicion' => 'integer',
        'idStatus' => 'integer'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class)->using(UsuarioServicio::class);
    }
}
