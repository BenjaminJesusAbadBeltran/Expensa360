<?php

namespace App\Http\Controllers\Api;

use App\Laravue\Models\Reporte;
use Illuminate\Http\Request;
use App\Http\Resources\ReporteResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


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

    // Devuelve la lista de tablas de la base de datos
    // public function getTables()
    // {
    //     // Obtener las tablas de la base de datos
    //     $tables = DB::select('SHOW TABLES');
    //     dd($tables);

    //     // Listado de tablas a excluir
    //     $excludedTables = [
    //         'failed_jobs',
    //         'migrations',
    //         'password_resets',
    //         'personal_access_tokens',
    //         'role_has_permissions',
    //         'model_has_permissions',
    //         'model_has_roles',
            
    //     ];

    //     // Filtrar las tablas excluidas
    //     $filteredTables = array_filter($tables, function($table) use ($excludedTables) {
    //         return !in_array($table->Tables_in_laravel, $excludedTables);
    //     });
    //     dd($filteredTables);

    //     // Formatear la respuesta para que sea un array de strings
    //     $formattedTables = array_map(function($table) {
    //         return $table->Tables_in_laravel;
    //     }, $filteredTables);

    //     return response()->json($formattedTables);
    // }

    public function fetchTables()
    {
        try {
            // Obtener las tablas de la base de datos
            $tables = DB::select('SHOW TABLES');

            // Listado de tablas a excluir
            $excludedTables = [
                'failed_jobs',
                'migrations',
                'password_resets',
                'personal_access_tokens',
                'role_has_permissions',
                'model_has_permissions',
                'model_has_roles',
                'permissions',
                'roles',
                'usuario_propiedad',
                'reportes',
            ];

            // Filtrar las tablas excluidas
            $filteredTables = array_filter($tables, function($table) use ($excludedTables) {
                return !in_array($table->Tables_in_laravel, $excludedTables);
            });

            // Formatear la respuesta para que sea un array de strings
            $formattedTables = array_map(function($table) {
                return $table->Tables_in_laravel;
            }, $filteredTables);

            return response()->json(array_values($formattedTables)); // Asegúrate de devolver un array de strings
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchColumns($table)
    {
        try {
            // Obtener las columnas de la tabla especificada
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
    
            // Listado de columnas a excluir
            $excludedColumns = [
                'created_at',
                'updated_at',
                'deleted_at',
                'status'
            ];
    
            // Filtrar las columnas excluidas
            $filteredColumns = array_filter($columns, function($column) use ($excludedColumns) {
                return !in_array($column, $excludedColumns);
            });
    
            // Si la tabla es 'users', reemplazar 'idPropiedad' con 'nombrePropiedad'
            if ($table === 'pagos') {
                $filteredColumns = array_map(function($column) {
                    return $column === 'idPropiedad' ? 'nombrePropiedad' : $column;
                }, $filteredColumns);
            }
            return response()->json(array_values($filteredColumns));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchTableData($table)
    {
        try {
            // Obtener los datos de la tabla especificada
            $data = DB::table($table)->get();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}