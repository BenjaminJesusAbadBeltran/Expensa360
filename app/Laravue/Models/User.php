<?php

namespace App\Laravue\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Laravue\Models\Servicio;
use App\Laravue\Models\UsuarioServicio;
use App\Laravue\Models\Expensa;
use App\Laravue\Models\UsuarioExpensa;
use App\Laravue\Models\Propiedad;
use App\Laravue\Models\UsuarioPropiedad;
use App\Laravue\Models\Pago;
use App\Laravue\Models\UsuarioPago;

/**
 * Class User
 *
 * @property string $nombre
 * @property string $email
 * @property string $password
 * @property Role[] $roles
 *
 * @method static User create(array $user)
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens;

    protected $table = 'users';

    protected $primaryKey = 'idUsuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'email', 'password', 'google_id','email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set permissions guard to API by default
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * @inheritdoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @inheritdoc
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if user has a permission
     * @param String
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        foreach ($this->roles as $role) {
            if (in_array($permission, $role->permissions->pluck('name')->toArray())) {
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
            if ($role->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class)->using(UsuarioServicio::class);
    }

    public function expensas()
    {
        return $this->belongsToMany(Expensa::class, 'usuario_expensa', 'idUsuario', 'idExpensa')->using(UsuarioExpensa::class);
    }

    public function propiedades()
    {
        return $this->belongsToMany(Propiedad::class)->using(UsuarioPropiedad::class);
    }

    public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'usuario_pago', 'idUsuario', 'idPago')->using(UsuarioPago::class);
    }
}
