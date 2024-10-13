<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\ServicioAgua;
use Illuminate\Http\Request;
use App\Http\Resources\ServicioAguaResource;
use Illuminate\Support\Arr;

class ServicioAguaController extends BaseController
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
        $servicioAguaQuery = ServicioAgua::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $servicioAguaQuery->where('idServicioAgua', 'LIKE', '%' . $keyword . '%');
        }

        return ServicioAguaResource::collection($servicioAguaQuery->paginate($limit));
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
            'montoPagar' => 'required|numeric',
            'fechaMedicion' => 'required|date',
            'medicion' => 'required|numeric',
            'previaMedicion' => 'required|numeric',
            'idStatus' => 'required|integer',
        ]);

        $servicioAgua = ServicioAgua::create($validatedData);
        return new ServicioAguaResource($servicioAgua);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\ServicioAgua  $servicioAgua
     * @return \Illuminate\Http\Response
     */
    public function show(ServicioAgua $servicioAgua)
    {
        return new ServicioAguaResource($servicioAgua);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\ServicioAgua  $servicioAgua
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ServicioAgua $servicioAgua)
    {
        $validatedData = $request->validate([
            'montoPagar' => 'required|numeric',
            'fechaMedicion' => 'required|date',
            'medicion' => 'required|numeric',
            'previaMedicion' => 'required|numeric',
            'idStatus' => 'required|integer',
        ]);

        $servicioAgua->update($validatedData);
        return new ServicioAguaResource($servicioAgua);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\ServicioAgua  $servicioAgua
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServicioAgua $servicioAgua)
    {
        // Cambiar el idStatus a 0
        $servicioAgua->idStatus = 0;
        $servicioAgua->save();

        return response()->json(['message' => 'ServicioAgua updated to idStatus 0'], 200);
    }
}