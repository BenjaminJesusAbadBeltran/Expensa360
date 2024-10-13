<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpensaResource extends JsonResource
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
            'idExpensa' => $this->idExpensa,
            'idPropiedad' => $this->idPropiedad,
            'montoPagar' => number_format((float)$this->montoPagar, 2, '.', ''),
            'fechaVencimiento' => $this->fechaVencimiento->format('d-m-Y'),
            'idStatus' => $this->idStatus,
            'propiedad' => new PropiedadResource($this->whenLoaded('propiedad')),
            'usuarios' => UserResource::collection($this->whenLoaded('usuarios')), // Asegúrate de formatear correctamente los datos de los usuarios
        ];
    }
}
