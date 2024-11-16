<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Laravue\Models\Expensa;
use App\Laravue\Models\Propiedad;
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
        try {
            $searchParams = $request->all();
            $expensaQuery = Expensa::query();
            $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
            $keyword = Arr::get($searchParams, 'keyword', '');
            $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'
            $propertyId = Arr::get($searchParams, 'propertyId', null); // Obtener el ID de la propiedad

            if (!empty($keyword)) {
                $expensaQuery->where(function($query) use ($keyword) {
                    $query->where('idExpensa', 'LIKE', '%' . $keyword . '%')
                          ->orWhereHas('propiedad', function($q) use ($keyword) {
                              $q->where('nombre', 'LIKE', '%' . $keyword . '%');
                          });
                });
            }
    
            if (!is_null($status)) {
                $expensaQuery->where('status', $status);
            }

            if (!is_null($propertyId)) {
                $expensaQuery->where('idPropiedad', $propertyId);
            }
    
            return ExpensaResource::collection($expensaQuery->paginate($limit));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            'monto' => 'required|numeric',
            'mes_gestion' => 'required|string',
        ]);

        // Set default value for status if not provided
        $validatedData['status'] = $validatedData['status'] ?? 'Activo';

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
        'monto' => 'required|numeric',
        'montoPagado' => 'optional|numeric',
        'montoPendiente' => 'optional|numeric',
        'mes_gestion' => 'required|string',
        'status' => 'required|string|in:Activo,Borrado', // Validar el estado
    ]);

    // Actualizar el estado de la expensa y otros campos
    $expensa->idPropiedad = $validatedData['idPropiedad'];
    $expensa->monto = $validatedData['monto'];
    $expensa->MontoPagado = $validatedData['montoPagado'];
    $expensa->MontoPendiente = $validatedData['montoPendiente'];
    $expensa->mes_gestion = $validatedData['mes_gestion'];
    $expensa->status = $validatedData['status'];
    $expensa->save();

    // Asignar usuarios a la expensa
    if (isset($validatedData['usuarios'])) {
        $expensa->usuarios()->sync($validatedData['usuarios']);
    }

    return new ExpensaResource($expensa);
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Laravue\Models\Expensa  $expensa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expensa $expensa)
    {
        $expensa->status = 'Borrado';
        $expensa->save();

        return response()->json(['message' => 'Expensa borrada'], 200);
    }

    public function getProperties(Request $request)
    {
        try {
            $searchParams = $request->all();
            $expensaQuery = Expensa::query();
            $idPropiedad = Arr::get($searchParams, 'idPropiedad', null);

            if (!is_null($idPropiedad)) {
                $expensaQuery->where('idPropiedad', $idPropiedad);
            } else {
                return response()->json(['error' => 'ID de propiedad no proporcionado.'], 400);
            }

            $result = $expensaQuery->get();
            return ExpensaResource::collection($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}
