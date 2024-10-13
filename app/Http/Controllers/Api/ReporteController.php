<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Reporte;
use Illuminate\Http\Request;
use App\Http\Resources\ReporteResource;
use Illuminate\Support\Arr;

class ReporteController extends BaseController
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
        $reporteQuery = Reporte::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $reporteQuery->where('idReporte', 'LIKE', '%' . $keyword . '%');
        }

        return ReporteResource::collection($reporteQuery->paginate($limit));
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
            'idGenerador' => 'required|integer',
            'tipo_reporte' => 'required|string',
            'datos_generados' => 'required|string',
            'idStatus' => 'required|integer',
        ]);

        $reporte = Reporte::create($validatedData);
        return new ReporteResource($reporte);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\Reporte  $reporte
     * @return \Illuminate\Http\Response
     */
    public function show(Reporte $reporte)
    {
        return new ReporteResource($reporte);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\Reporte  $reporte
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reporte $reporte)
    {
        $validatedData = $request->validate([
            'idGenerador' => 'required|integer',
            'tipo_reporte' => 'required|string',
            'datos_generados' => 'required|string',
            'idStatus' => 'required|integer',
        ]);

        $reporte->update($validatedData);
        return new ReporteResource($reporte);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Reporte  $reporte
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reporte $reporte)
    {
        // Cambiar el idStatus a 0
        $reporte->idStatus = 0;
        $reporte->save();

        return response()->json(['message' => 'Reporte updated to idStatus 0'], 200);
    }
}