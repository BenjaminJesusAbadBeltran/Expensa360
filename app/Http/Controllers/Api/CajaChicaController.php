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

        if (!empty($keyword)) {
            $cajaChicaQuery->where('idCajaChica', 'LIKE', '%' . $keyword . '%');
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
        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules()
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $cajaChica = CajaChica::create([
                'total' => $params['total'],
            ]);

            return new CajaChicaResource($cajaChica);
        }
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
    public function update(Request $request, $id)
    {
        $cajaChica = CajaChica::find($id);

        if (!$cajaChica) {
            return response()->json(['error' => 'CajaChica not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }
        
        $cajaChica->update($validator->validated());
        return new CajaChicaResource($cajaChica);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CajaChica $cajaChica
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cajaChica = CajaChica::find($id);

        if ($cajaChica) {
            $cajaChica->delete();
            return response()->json(['message' => 'CajaChica deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'CajaChica not found'], 404);
        }
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'total' => 'required|numeric',
        ];
    }
}
