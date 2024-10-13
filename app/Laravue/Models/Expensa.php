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
        'montoPagar',
        'fechaVencimiento',
        'idStatus',
    ];

    protected $casts = [
        'montoPagar' => 'decimal:2',
        'fechaVencimiento' => 'date',
        'idStatus' => 'integer',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Propiedad::class, 'idPropiedad', 'idPropiedad');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_expensa', 'idExpensa', 'idUsuario')->using(UsuarioExpensa::class);
    }
}
