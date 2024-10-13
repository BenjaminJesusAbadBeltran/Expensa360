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
        'idStatus',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class)->using(UsuarioPropiedad::class);
    }
}
