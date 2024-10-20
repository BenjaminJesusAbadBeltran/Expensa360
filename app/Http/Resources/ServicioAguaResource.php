<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServicioAguaResource extends JsonResource
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
            'idServicio' => $this->idServicio,
            'idPropiedad' => $this->idPropiedad,
            'montoPagar' => number_format((float)$this->montoPagar, 2, '.', ''),
            'fechaMedicion' => $this->fechaMedicion->format('Y-m-d'),
            'medicion' => $this->medicion,
            'previaMedicion' => $this->previaMedicion,
            'status' => $this->status,
            'propiedad' => new PropiedadResource($this->whenLoaded('propiedad')),
        ];
    }
}
