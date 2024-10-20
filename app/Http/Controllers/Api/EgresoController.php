<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Egreso;
use Illuminate\Http\Request;
use App\Http\Resources\EgresoResource;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EgresoController extends BaseController
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
        $egresoQuery = Egreso::query(); // Filtrar por status = Activo
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'

        if (!empty($keyword)) {
            $egresoQuery->where('idEgreso', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($status)) {
            $egresoQuery->where('status', $status);
        }

        return EgresoResource::collection($egresoQuery->paginate($limit));
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
        'idCajaChica' => 'required|integer',
        'concepto' => 'required|string',
        'monto' => 'required|numeric',
        'fechaEgreso' => 'required|date',
        'status' => 'required|string',
    ]);

    // Convert fechaEgreso to the correct format
    $validatedData['fechaEgreso'] = Carbon::parse($validatedData['fechaEgreso'])->format('Y-m-d');

    $egreso = Egreso::create($validatedData);
    return new EgresoResource($egreso);
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\Egreso  $egreso
     * @return \Illuminate\Http\Response
     */
    public function show(Egreso $egreso)
    {
        return new EgresoResource($egreso);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\Egreso  $egreso
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Egreso $egreso)
{
    $validatedData = $request->validate([
        'idCajaChica' => 'required|integer',
        'concepto' => 'required|string',
        'monto' => 'required|numeric',
        'fechaEgreso' => 'required|date',
        'status' => 'required|string',
    ]);

    // Convert fechaEgreso to the correct format
    $validatedData['fechaEgreso'] = Carbon::parse($validatedData['fechaEgreso'])->format('Y-m-d');

    $egreso->update($validatedData);
    return new EgresoResource($egreso);
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Egreso  $egreso
     * @return \Illuminate\Http\Response
     */
    public function destroy(Egreso $egreso)
    {

        // Cambiar el idStatus a 0
        $egreso->status = 'Borrado';
        $egreso->save();

        return response()->json(['message' => 'Egreso updated to idStatus 0'], 200);
    }
}