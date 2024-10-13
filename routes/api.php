<?php

use App\Http\Resources\UserResource;
use App\Http\Resources\ExpensaResource;
use App\Http\Resources\PropiedadResource;
use App\Http\Resources\ServicioResource;
use App\Http\Resources\MetodoPagoResource;
use App\Http\Resources\CajaChicaResource;
use App\Http\Resources\EgresoResource;
use App\Http\Resources\PagoResource;
use App\Http\Resources\ReporteResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Laravue\Faker;
use \App\Laravue\JsonResponse;
use \App\Laravue\Acl;
use Laravel\Socialite\Facades\Socialite;
use App\Laravue\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::namespace('Api')->group(function() {

    Route::post('auth/login', 'AuthController@login');

    Route::group(['middleware' => ['web']], function () {
        Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect']);
        Route::get('auth/google/callback', [GoogleAuthController::class, 'callback']);
        Route::get('auth/user', 'AuthController@user');
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {
        // Auth routes
        Route::get('auth/user', 'AuthController@user');
        Route::post('auth/logout', 'AuthController@logout');
        
        Route::get('/user', function (Request $request) {
            return new UserResource($request->user());
        });
        
        Route::get('/expensa', function (Request $request) {
            return new ExpensaResource($request->expensa());
        });

        Route::get('/propiedad', function (Request $request) {
            return new PropiedadResource($request->propiedad());
        });

        Route::get('/servicio-agua', function (Request $request) {
            return new ServicioResource($request->servicio());
        });

        Route::get('/metodo-pago', function (Request $request) {
            return new MetodoPagoResource($request->metodo());
        });

        Route::get('/caja-chica', function (Request $request) {
            return new CajaChicaResource($request->cajachica());
        });

        Route::get('/egreso', function (Request $request) {
            return new EgresoResource($request->egreso());
        });

        Route::get('/pago', function (Request $request) {
            return new PagoResource($request->pago());
        });

        Route::get('/reporte', function (Request $request) {
            return new ReporteResource($request->reporte());
        });

        // Api resource routes
        Route::apiResource('roles', 'RoleController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);

        // Custom routes
        Route::apiResource('users', 'UserController')->middleware('permission:' . Acl::PERMISSION_USER_MANAGE);
        Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' .Acl::PERMISSION_PERMISSION_MANAGE);
        Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::put('users/{user}', 'UserController@update');

        // Additional resource routes
        Route::apiResource('propiedades', 'PropiedadController')->middleware('permission:' . Acl::PERMISSION_PROPERTY_MANAGE);
        Route::apiResource('expensas', 'ExpensaController')->middleware('permission:' . Acl::PERMISSION_EXPENSE_MANAGE);
        Route::apiResource('servicio-agua', 'ServicioAguaController')->middleware('permission:' . Acl::PERMISSION_WATER_SERVICE_MANAGE);
        Route::apiResource('metodos-pago', 'MetodoPagoController')->middleware('permission:' . Acl::PERMISSION_PAYMENT_METHOD_MANAGE);
        Route::apiResource('caja-chica', 'CajaChicaController')->middleware('permission:' . Acl::PERMISSION_PETTY_CASH_MANAGE);
        Route::apiResource('egresos', 'EgresoController')->middleware('permission:' . Acl::PERMISSION_OUTFLOW_MANAGE);
        Route::apiResource('pagos', 'PagoController')->middleware('permission:' . Acl::PERMISSION_PAYMENT_MANAGE);
        Route::apiResource('reportes', 'ReporteController')->middleware('permission:' . Acl::PERMISSION_REPORT_MANAGE);
        
    });
});

