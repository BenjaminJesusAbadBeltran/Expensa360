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
            'idUsuario' => $this->idUsuario,
            'idPropiedad' => $this->idPropiedad,
            'idMetodoPago' => $this->idMetodoPago,
            'montoTotal' => $this->montoTotal,
            'fechaPago' => $this->fechaPago->format('Y-m-d H:i'),
            'observaciones' => $this->observaciones,
            'status' => $this->status,
            'metodoPago' => new MetodoPagoResource($this->whenLoaded('metodoPago')),
            'propiedad' => new PropiedadResource($this->whenLoaded('propiedad')),
            'usuarios' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
