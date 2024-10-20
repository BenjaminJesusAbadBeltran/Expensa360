<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CajaChicaResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\CajaChica;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Validator;

/**
 * Class CajaChicaController
 *
 * @package App\Http\Controllers\Api
 */
class CajaChicaController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $cajaChicaQuery = CajaChica::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'

        if (!empty($keyword)) {
            $cajaChicaQuery->where('idCajaChica', 'LIKE', '%' . $keyword . '%');
        }
        if (!is_null($status)) {
            $cajaChicaQuery->where('status', $status);
        }

        return CajaChicaResource::collection($cajaChicaQuery->paginate($limit));
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
            'saldoInicial' => 'required|numeric',
            'saldoActual' => 'required|numeric',
            'saldoFinal' => 'required|numeric',
            'fecha_Inicial' => 'required|date',
            'fecha_Final' => 'required|date',
        ]);

        $validatedData['status'] = $validatedData['status'] ?? 'Activo';

        $cajaChica = CajaChica::create($validatedData);

        return new CajaChicaResource($cajaChica);
    }

    /**
     * Display the specified resource.
     *
     * @param  CajaChica $cajaChica
     * @return CajaChicaResource|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cajaChica = CajaChica::find($id);
        if (!$cajaChica) {
            return response()->json(['message' => 'Not Found'], 404);
            
        } else {
            return new CajaChicaResource($cajaChica);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param CajaChica $cajaChica
     * @return CajaChicaResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CajaChica $cajaChica)
    {
        $validatedData = $request->validate([
            'saldoInicial' => 'required|numeric',
            'saldoActual' => 'required|numeric',
            'saldoFinal' => 'required|numeric',
            'fecha_Inicial' => 'required|date',
            'fecha_Final' => 'required|date',
            'status' => 'required|string',
        ]);
        
        $cajaChica->update($validatedData);
        return new CajaChicaResource($cajaChica);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CajaChica $cajaChica
     * @return \Illuminate\Http\Response
     */
    public function destroy(CajaChica $cajaChica)
    {
        $cajaChica->status = 'Borrado';
        $cajaChica->save();

        return response()->json(['message' => 'CajaChica updated to idStatus 0'], 200);
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'saldoInicial' => 'required|numeric',
            'saldoActual' => 'required|numeric',
            'saldoFinal' => 'required|numeric',
            'fecha_Inicial' => 'required|date',
            'fecha_Final' => 'required|date',
        ];
    }
}
