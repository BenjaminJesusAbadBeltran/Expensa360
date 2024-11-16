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
        $propiedadQuery = Propiedad::with('usuarios'); // Añadir 'usuarios' a la carga ansiosa
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $status = Arr::get($searchParams, 'status', 'Activo'); // Default to 'Activo'

        if (!empty($keyword)) {
            $propiedadQuery->where('nombre', 'LIKE', '%' . $keyword . '%');
        }

        if (!is_null($status)) {
            $propiedadQuery->where('status', $status);
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
                'status' => 'required|string',
                'usuarios' => 'array', // Validar que usuarios sea un array
                'usuarios.*' => 'exists:users,idUsuario', // Validar que cada usuario exista en la tabla users
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $propiedad = Propiedad::create([
                'numero' => $params['numero'],
                'piso' => $params['piso'],
                'nombre' => $params['nombre'],
                'tipo_propiedad' => $params['tipo_propiedad'],
                'status' => $params['status'],
            ]);

            // Asociar usuarios a la propiedad
            if (isset($params['usuarios'])) {
                $propiedad->usuarios()->sync($params['usuarios']);
            }

            return new PropiedadResource($propiedad);
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
        $propiedad = Propiedad::with('usuarios')->find($id);

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
        //Log::info('Incoming request data:', $request->all()); // Registro de los datos recibidos
    
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string',
            'piso' => 'required|string',
            'nombre' => 'required|string',
            'tipo_propiedad' => 'required|string',
            'status' => 'required|string',
            'usuarios' => 'array', // Validar que usuarios sea un array
            'usuarios.*' => 'exists:users,idUsuario', // Validar que cada usuario exista en la tabla users
        ]);
    
        $validatedData = $validator->validated();
        //Log::info('Validated data:', $validatedData);
    
        $propiedad->update($validatedData);
    
        // Asociar usuarios a la propiedad
        if (isset($request->usuarios)) {
            //Log::info('Syncing users:', $request->usuarios);
            $propiedad->usuarios()->sync($request->usuarios);
        }
    
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

        $propiedad->status = 'Borrado';
        $propiedad->save();

        return response()->json(['message' => 'Propiedad deleted successfully'], 200);
    }
    
}