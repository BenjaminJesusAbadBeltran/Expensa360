<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetallePagoResource;
use App\Laravue\Models\DetallePago;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DetallePagoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $detallePagos = DetallePago::all();
        return DetallePagoResource::collection($detallePagos);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'idPago' => 'required|integer',
            'idExpensa' => 'required|integer',
            'idServicio' => 'required|integer',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric',
            'mes' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        $detallePago = DetallePago::create($validatedData);

        return new DetallePagoResource($detallePago);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\DetallePago  $detallePago
     * @return \Illuminate\Http\Response
     */
    public function show(DetallePago $detallePago)
    {
        return new DetallePagoResource($detallePago);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\DetallePago  $detallePago
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DetallePago $detallePago)
    {
        $validatedData = $request->validate([
            'idPago' => 'integer',
            'idExpensa' => 'integer',
            'idServicio' => 'integer',
            'concepto' => 'string|max:255',
            'monto' => 'numeric',
            'mes' => 'string|max:255',
            'status' => 'string|max:255',
        ]);

        $detallePago->update($validatedData);

        return new DetallePagoResource($detallePago);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\DetallePago  $detallePago
     * @return \Illuminate\Http\Response
     */
    public function destroy(DetallePago $detallePago)
    {
        $detallePago->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function getExpensasByPropiedad($idPropiedad)
    {
        // Obtener las expensas asociadas a la propiedad a través de la tabla `detalle_pagos`
        $expensas = Expensa::where('idPropiedad', $idPropiedad)
            ->whereHas('detallePagos', function ($query) {
                $query->where('concepto', 'expensa');
            })->with('detallePagos')
            ->get();

        // Verificar si se encontraron expensas
        if ($expensas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron expensas asociadas a esta propiedad en detalle de pagos.'
            ], 404);
        }

        // Retornar las expensas encontradas junto con los detalles de pago
        return response()->json($expensas);
    }
}