<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Laravue\Models\User;
use Socialite;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;
use App\Laravue\Models\Role;

class GoogleAuthController extends BaseController
{
    public function redirect()
    {
        return Socialite::driver("google")->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver("google")->stateless()->user();

        // Asumimos que el nombre completo está en el formato "Nombre ApellidoPaterno ApellidoMaterno"
        $nameParts = explode(' ', $googleUser->getName());
        $nombre = $nameParts[0] ?? 'Nombre';
        $apellidoPaterno = $nameParts[1] ?? 'ApellidoPaterno';
        $apellidoMaterno = $nameParts[2] ?? 'ApellidoMaterno';

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'nombre' => $nombre,
                'apellidoPaterno' => $apellidoPaterno,
                'apellidoMaterno' => $apellidoMaterno,
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(Str::random(16)), // Usa bcrypt para hashear la contraseña
                'email_verified_at' => now(),
            ]);

        // Asignar el rol de Admin al usuario
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        Auth::login($user);

        //return response()->json(new JsonResponse(new UserResource($user)), Response::HTTP_OK);
        return redirect('/');
    }
}
