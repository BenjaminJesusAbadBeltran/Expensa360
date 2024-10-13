<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PagoResource extends JsonResource
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
            'idPago' => $this->idPago,
            'idMetodoPago' => $this->idMetodoPago,
            'idCajaChica' => $this->idCajaChica,
            'monto' => $this->monto,
            'fechaPago' => $this->fechaPago,
            'idStatus' => $this->idStatus,
            'metodoPago' => new MetodoPagoResource($this->whenLoaded('metodoPago')),
            'cajaChica' => new CajaChicaResource($this->whenLoaded('cajaChica')),
            'usuarios' => UserResource::collection($this->whenLoaded('usuarios')),
        ];
    }
}
