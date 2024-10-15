<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\MetodoPago;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Arr;
use App\Http\Resources\MetodoPagoResource;
use Validator;
use Illuminate\Support\Facades\Storage;

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
        $metodoPagoQuery = MetodoPago::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $idStatus = Arr::get($searchParams, 'idStatus', 1); // Default to 1

        if (!empty($keyword)) {
            $metodoPagoQuery->where('nombre', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($idStatus)) {
            $metodoPagoQuery->where('idStatus', $idStatus);
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
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'cuenta' => 'required|string|max:255',
            'idStatus' => 'required|integer|in:0,1',
            'imagen' => 'nullable|string', // Validar que la imagen es una cadena base64
        ]);

        $validatedData['idStatus'] = $validatedData['idStatus'] ?? 1;
    
        if (!empty($metodoPago['imagen'])) {
            $imageData = $metodoPago['imagen'];
            $fileName = 'metodo_pago_' . time() . '.png';
            Storage::disk('public')->put($fileName, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));
            $metodoPago['imagen'] = $fileName;
        }
    
        $metodoPago = new MetodoPago($validatedData);
        $metodoPago->save();

        return new MetodoPagoResource($metodoPago);
    
        // return response()->json(['message' => 'Método de pago creado con éxito', 'data' => new MetodoPagoResource($metodoPago)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Laravue\Models\MetodoPago  $metodoPago
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'MetodoPago not found'], 404);
        }

        return new MetodoPagoResource($metodoPago);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Laravue\Models\MetodoPago  $metodoPago
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'cuenta' => 'required|string|max:255',
            'idStatus' => 'required|integer',
            'imagen' => 'nullable|string', // Validar que la imagen es una cadena base64
        ]);

        $metodoPago = MetodoPago::findOrFail($id);
        $metodoPago->fill($validatedData);

        if (!empty($request->imagen)) {
            // Eliminar la imagen anterior si existe
            if ($metodoPago->imagen) {
                Storage::disk('public')->delete($metodoPago->imagen);
            }
            $imageData = $request->imagen;
            $fileName = 'metodo_pago_' . time() . '.png';
            Storage::disk('public')->put($fileName, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)));
            $metodoPago->imagen = $fileName;
        }

        $metodoPago->save();

        return new MetodoPagoResource($metodoPago);
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

        // Delete imagen if exists
        if ($metodoPago->imagen) {
            Storage::disk('public')->delete($metodoPago->imagen);
            $metodoPago->imagen = null; // Clear the imagen field
        }

        $metodoPago->idStatus = 0; // Desactivar el método de pago en lugar de eliminarlo
        $metodoPago->save();

        return new MetodoPagoResource($metodoPago);
    }
}
