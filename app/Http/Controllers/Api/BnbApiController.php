<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BnbapiController extends Controller
{
    public function obtenerToken()
    {
        $response = Http::asForm()->post('https://clientauthenticationapiv2.azurewebsites.net/api/v1/', [
            'a1' => json_encode([
                'accountId' => 'I8Bl1/IZBWyZk+qJCaMahw==',
                'authorizationId' => 'xGTy/5MpdpjgSeuBPIEVwA=='
            ]),
            'a2' => 'token'
        ]);

        return $response->json();
    }

    public function obtenerBalance(Request $request)
    {
        $token = $request->input('token');
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->post('https://www.bnb.com.bo/PortalBNB/Api/ConsumirServicio', [
            // Aquí puedes agregar los parámetros necesarios para consultar el balance
        ]);

        return $response->json();
    }
}