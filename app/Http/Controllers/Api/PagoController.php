<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Pago;
use Illuminate\Http\Request;
use App\Http\Resources\PagoResource;
use Illuminate\Support\Arr;

class PagoController extends BaseController
{
    const ITEM_PER_PAGE = 10;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $pagoQuery = Pago::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'

        if (!empty($keyword)) {
            $pagoQuery->where('idPago', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($status)) {
            $pagoQuery->where('status', $status);
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
            'idPropiedad' => 'required|integer',
            'idUsuario' => 'required|integer',
            'montoTotal' => 'required|numeric|min:0',
            'fechaPago' => 'required|date',
            'status' => 'required|string',
        ]);

        $validatedData['status'] = $validatedData['status'] ?? 'Activo';

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
            'idMetodoPago' => 'required|integer|exists:metodos_pago,idMetodo',
            'idPropiedad' => 'required|integer|exists:propiedades,idPropiedad',
            'idUsuario' => 'required|integer|exists:users,idUsuario',
            'montoTotal' => 'required|numeric|min:0',
            'fechaPago' => 'required|date',
            'status' => 'required|string',
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
        // Cambiar el status a 'Borrado'
        $pago->status = 'Borrado';
        $pago->save();

        return response()->json(['message' => 'Pago updated to status Borrado'], 200);
    }
}
