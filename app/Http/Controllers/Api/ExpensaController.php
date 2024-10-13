<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Laravue\Models\Expensa;
use App\Laravue\Models\Propiedad; // Asegúrate de importar el modelo Propiedad
use App\Http\Resources\ExpensaResource;
use Illuminate\Support\Arr;

class ExpensaController extends BaseController
{
    const ITEM_PER_PAGE = 10;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {   
        $searchParams = $request->all();
        $expensaQuery = Expensa::with('usuarios'); // Añadir 'usuarios' a la carga ansiosa
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $idStatus = Arr::get($searchParams, 'idStatus', 1); // Default to 1

        if (!empty($keyword)) {
            $expensaQuery->where('idExpensa', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($idStatus)) {
            $expensaQuery->where('idStatus', $idStatus);
        }

        return ExpensaResource::collection($expensaQuery->paginate($limit));
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
            'idPropiedad' => 'required|integer',
            'montoPagar' => 'required|numeric',
            'fechaVencimiento' => 'required|date',
            'idStatus' => 'required|integer|in:0,1',
            'usuarios' => 'array', // Validar que usuarios sea un array
            'usuarios.*' => 'integer|exists:users,idUsuario', // Validar que cada usuario exista
        ]);

        // Set default value for idStatus if not provided
        $validatedData['idStatus'] = $validatedData['idStatus'] ?? 1;

        $expensa = Expensa::create($validatedData);

        // Asignar usuarios a la expensa
        if (isset($validatedData['usuarios'])) {
            $expensa->usuarios()->sync($validatedData['usuarios']);
        }
        return new ExpensaResource($expensa);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\Expensa  $expensa
     * @return \Illuminate\Http\Response
     */
    public function show(Expensa $expensa)
    {
        return new ExpensaResource($expensa);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\Expensa  $expensa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expensa $expensa)
    {
        $validatedData = $request->validate([
            'idPropiedad' => 'required|integer|exists:propiedades,idPropiedad',
            'montoPagar' => 'required|numeric',
            'fechaVencimiento' => 'required|date',
            'idStatus' => 'required|integer',
            'usuarios' => 'array', // Validar que usuarios sea un array
            'usuarios.*' => 'integer|exists:users,idUsuario', // Validar que cada usuario exista
        ]);

        $expensa->update($validatedData);

        // Asignar usuarios a la expensa
        if (isset($validatedData['usuarios'])) {
            $expensa->usuarios()->sync($validatedData['usuarios']);
        }
        return new ExpensaResource($expensa);
        return response()->json(['message' => 'Expensa Actualizada'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Expensa  $expensa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expensa $expensa)
    {
        $expensa->idStatus = 0;
        $expensa->save();

        return response()->json(['message' => 'Expensa borrada'], 200);
    }

}