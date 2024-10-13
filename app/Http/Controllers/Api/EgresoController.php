<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Egreso;
use Illuminate\Http\Request;
use App\Http\Resources\EgresoResource;
use Illuminate\Support\Arr;

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
        $egresoQuery = Egreso::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $egresoQuery->where('idEgreso', 'LIKE', '%' . $keyword . '%');
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
            'monto' => 'required|numeric',
            'idStatus' => 'required|integer',
        ]);

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
            'monto' => 'required|numeric',
            'idStatus' => 'required|integer',
        ]);

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
        $egreso->idStatus = 0;
        $egreso->save();

        return response()->json(['message' => 'Egreso updated to idStatus 0'], 200);
    }
}