<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Propiedad;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Arr;
use App\Http\Resources\PropiedadResource;
use Validator;

class PropiedadController extends BaseController
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
        $propiedadQuery = Propiedad::where('idStatus', 1); // Filtrar por idStatus = 1
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $propiedadQuery->where('nombre', 'LIKE', '%' . $keyword . '%');
        }

        return PropiedadResource::collection($propiedadQuery->paginate($limit));
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
            'numero' => 'required|string',
            'piso' => 'required|string',
            'nombre' => 'required|string|max:255',
            'tipo_propiedad' => 'required|string',
            'idStatus' => 'required|integer',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $metodoPago = Propiedad::create([
            'numero' => $params['numero'],
            'piso' => $params['piso'],
            'nombre' => $params['nombre'],
            'tipo_propiedad' => $params['tipo_propiedad'],
            'idStatus' => $params['idStatus'],
            ]);

            return new PropiedadResource($metodoPago);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\Propiedad  $propiedad
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $propiedad = Propiedad::find($id);

        if (!$propiedad) {
            return response()->json(['message' => 'Propiedad not found'], 404);
        }

        return new PropiedadResource($propiedad);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\Propiedad  $propiedad
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $propiedad = Propiedad::find($id);

        if (!$propiedad) {
            return response()->json(['message' => 'Propiedad not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'numero' => 'required|string',
            'piso' => 'required|string',
            'nombre' => 'required|string',
            'tipo_propiedad' => 'required|string',
            'idStatus' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        $propiedad->update($validator->validated());
        return new PropiedadResource($propiedad);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Propiedad  $propiedad
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $propiedad = Propiedad::find($id);

        if (!$propiedad) {
            return response()->json(['message' => 'Propiedad not found'], 404);
        }

        $propiedad->idStatus = 0;
        $propiedad->save();

        return response()->json(['message' => 'Propiedad deleted successfully'], 200);
    }
}