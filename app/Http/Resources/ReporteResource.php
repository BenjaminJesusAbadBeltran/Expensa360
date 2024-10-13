<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReporteResource extends JsonResource
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
            'idReporte' => $this->idReporte,
            'idGenerador' => $this->idGenerador,
            'tipo_reporte' => $this->tipo_reporte,
            'datos_generados' => $this->datos_generados,
            'idStatus' => $this->idStatus,
            'generador' => new UserResource($this->whenLoaded('generador')),
        ];
    }
}
