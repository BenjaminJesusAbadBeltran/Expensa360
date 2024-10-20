<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PropiedadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'idPropiedad' => $this->idPropiedad,
            'numero' => $this->numero,
            'piso' => $this->piso,
            'nombre' => $this->nombre,
            'tipo_propiedad' => $this->tipo_propiedad,
            'status' => $this->status,
            'usuarios' => UserResource::collection($this->whenLoaded('usuarios')),
        ];
    }
}
