<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CajaChicaResource;

class EgresoResource extends JsonResource
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
            'idEgreso' => $this->idEgreso,
            'idCajaChica' => $this->idCajaChica,
            'concepto' => $this->concepto,
            'monto' => $this->monto,
            'fechaEgreso' => $this->fechaEgreso,
            'status' => $this->status,
            'cajaChica' => new CajaChicaResource($this->whenLoaded('cajaChica')),
        ];
    }
}
