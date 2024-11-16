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
            'monto' => number_format((float)$this->monto, 2, '.', ''),
            'montoPagado' => number_format((float)$this->montoPagado, 2, '.', ''),
            'montoPendiente' => number_format((float)$this->montoPendiente, 2, '.', ''),
            'montoAhorro' => number_format((float)$this->montoAhorro, 2, '.', ''),
            'mes_gestion' => $this->mes_gestion,
            'estado' => $this->estado,
            'status' => $this->status,
            'propiedad' => new PropiedadResource($this->whenLoaded('propiedad')),
        ];
    }
}
