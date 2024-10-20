<?php

namespace App\Laravue\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class CajaChica extends Model
{
    use HasFactory, HasRoles;

    /**
     * Class CajaChica
     *
     * @property int $idCajaChica
     * @property float $total
     *
     * @method static CajaChica create(array $data)
     * @package App\Laravue\Models
     */
    protected $table = 'caja_chica';

    protected $primaryKey = 'idCajaChica';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'saldoInicial', 'saldoActual', 'saldoFinal', 'fecha_Inicial', 'fecha_Final', 'status'
    ];

    /**
     * Check if user has a permission
     * @param String
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->name === 'super_admin') {
                return true;
            }
        }
        return false;
    }
}