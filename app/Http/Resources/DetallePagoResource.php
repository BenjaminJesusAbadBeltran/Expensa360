<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetallePagoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'idDetallePago' => $this->idDetallePago,
            'idPago' => $this->idPago,
            'idExpensa' => $this->idExpensa,
            'idServicio' => $this->idServicio,
            'concepto' => $this->concepto,
            'monto' => $this->monto,
            'mes' => $this->mes,
            'status' => $this->status,
            'pago' => new PagoResource($this->whenLoaded('pago')),
            'expensa' => new ExpensaResource($this->whenLoaded('expensa')),
            'servicio' => new ServicioAguaResource($this->whenLoaded('servicio')),
        ];
    }
}