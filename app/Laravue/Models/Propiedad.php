<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $primaryKey = 'idPropiedad';

    protected $fillable = [
        'numero',
        'piso',
        'nombre',
        'tipo_propiedad',
        'status',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_propiedad', 'idPropiedad', 'idUsuario')->using(UsuarioPropiedad::class);
    }
}
