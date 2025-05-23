<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CajaChicaResource extends JsonResource
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
            'idCajaChica' => $this->idCajaChica,
            'saldoInicial' => $this->saldoInicial,
            'saldoActual' => $this->saldoActual,
            'saldoFinal' => $this->saldoFinal,
            'fecha_Inicial' => $this->fecha_Inicial,
            'fecha_Final' => $this->fecha_Final,
            'status' => $this->status,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ];
    }
}
