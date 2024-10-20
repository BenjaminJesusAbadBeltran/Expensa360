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
    
            if (!empty($keyword)) {
                $expensaQuery->where('idExpensa', 'LIKE', '%' . $keyword . '%');
            }
    
            if (!is_null($status)) {
                $expensaQuery->where('status', $status);
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
            'mes' => 'required|string',
        ]);

        // Formatear el campo 'mes' para que incluya el primer día del mes
        $validatedData['mes'] = date('Y-m-10', strtotime($validatedData['mes']));

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
            'mes' => 'required|string',
            'status' => 'required|string',
        ]);

        // Formatear el campo 'mes' para que incluya el primer día del mes
        $validatedData['mes'] = date('Y-m-10', strtotime($validatedData['mes']));

        $expensa->update($validatedData);

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
}
