<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Pago;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\PagoResource;
use Illuminate\Support\Arr;

class PagoController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $pagoQuery = Pago::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $pagoQuery->where('idPago', 'LIKE', '%' . $keyword . '%');
        }

        return PagoResource::collection($pagoQuery->paginate($limit));
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
            'idMetodoPago' => 'required|integer',
            'idCajaChica' => 'required|integer',
            'monto' => 'required|numeric',
            'fechaPago' => 'required|date',
            'idStatus' => 'required|integer',
        ]);

        $pago = Pago::create($validatedData);
        return new PagoResource($pago);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\Pago  $pago
     * @return \Illuminate\Http\Response
     */
    public function show(Pago $pago)
    {
        return new PagoResource($pago);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\Pago  $pago
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pago $pago)
    {
        $validatedData = $request->validate([
            'idMetodoPago' => 'required|integer',
            'idCajaChica' => 'required|integer',
            'monto' => 'required|numeric',
            'fechaPago' => 'required|date',
            'idStatus' => 'required|integer',
        ]);

        $pago->update($validatedData);
        return new PagoResource($pago);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Pago  $pago
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pago $pago)
    {
        // Cambiar el idStatus a 0
        $pago->idStatus = 0;
        $pago->save();

        return response()->json(['message' => 'Pago updated to idStatus 0'], 200);
    }
}