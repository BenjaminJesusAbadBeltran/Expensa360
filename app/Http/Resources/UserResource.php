<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'idUsuario' => $this->idUsuario,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name')->toArray(),
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),
            'avatar' => 'https://i.pravatar.cc',
        ];
    }
}
