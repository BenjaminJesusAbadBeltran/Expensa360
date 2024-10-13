<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\MetodoPago;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Arr;
use App\Http\Resources\MetodoPagoResource;
use Validator;

class MetodoPagoController extends BaseController
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
        $metodoPagoQuery = MetodoPago::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $metodoPagoQuery->where('nombre', 'LIKE', '%' . $keyword . '%');
        }

        return MetodoPagoResource::collection($metodoPagoQuery->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'nombre' => 'required|string|max:255',
            'cuenta' => 'required|string|max:255',
            'idStatus' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $metodoPago = MetodoPago::create([
            'nombre' => $params['nombre'],
            'cuenta' => $params['cuenta'],
            'idStatus' => $params['idStatus'],
            ]);

            return new MetodoPagoResource($metodoPago);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\MetodoPago  $metodoPago
     * @return \Illuminate\Http\Response
     */
    // MetodoPagoController.php
    public function show($id)
    {
        $metodoPago = MetodoPago::find($id);
    
        if (!$metodoPago) {
            return response()->json(['message' => 'MetodoPago not found'], 404);
        }

        return response()->json(['data' => $metodoPago], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\MetodoPago  $metodoPago
     * @return \Illuminate\Http\Response
     */
    // MetodoPagoController.php
    public function update(Request $request, $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'MetodoPago not found'], 404);
        }

        $metodoPago->nombre = $request->input('nombre');
        $metodoPago->cuenta = $request->input('cuenta');
        $metodoPago->idStatus = $request->input('idStatus');
        $metodoPago->save();

        return response()->json(['data' => $metodoPago], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\MetodoPago  $metodoPago
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'MetodoPago not found'], 404);
        }

        $metodoPago->idStatus = 0;
        $metodoPago->save();

        return response()->json(['message' => 'MetodoPago deleted successfully'], 200);
    }
}
