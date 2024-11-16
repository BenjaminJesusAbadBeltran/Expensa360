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
        $servicioAguaQuery = ServicioAgua::with('propiedad');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'

        if (!empty($keyword)) {
            $servicioAguaQuery->where('idServicioAgua', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($status)) {
            $servicioAguaQuery->where('status', $status);
        }

        $servicios = $servicioAguaQuery->paginate($limit);

    // Transformar los datos para incluir el nombre de la propiedad
        $servicios->getCollection()->transform(function ($servicio) {
            $servicio->nombrePropiedad = $servicio->propiedad ? $servicio->propiedad->nombre : 'N/A';
            return $servicio;
        });

        return ServicioAguaResource::collection($servicios);    
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Verificar si fechaMedicion es nulo o vacío
        if (!$request->filled('fechaMedicion') || empty($request->input('fechaMedicion'))) {
            $request->merge(['fechaMedicion' => now()->toDateString()]);
        }

        $validatedData = $request->validate([
            'idPropiedad' => 'required|integer|exists:propiedades,idPropiedad',
            'montoPagar' => 'required|numeric',
            'fechaMedicion' => 'required|date',
            'medicion' => 'required|numeric',
            'previaMedicion' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $validatedData['status'] = $validatedData['status'] ?? 'Activo';

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
            'idPropiedad' => 'required|integer|exists:propiedades,idPropiedad',
            'montoPagar' => 'required|numeric',
            'fechaMedicion' => 'required|date',
            'medicion' => 'required|numeric',
            'previaMedicion' => 'required|numeric',
            'status' => 'required|string',
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
        // Cambia el status a 0
        $servicioAgua->status = 'Borrado';
        $servicioAgua->save();

        return response()->json(['message' => 'ServicioAgua updated to idStatus 0'], 200);
    }
}