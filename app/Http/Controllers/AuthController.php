<?php

namespace App\Http\Controllers;

use App\Exceptions\Http\InvalidCredentialsException;
use App\Exceptions\Http\UnverifiedAccountException;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
//use JWTAuth;
//use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): UserResource
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        return new UserResource($user);
    }


    public function login(Request $request)
    {
        $credentials = $request->only( 'password', 'email');

        if (Auth::attempt($credentials)) {
            $token = JWTAuth::attempt([
               "email" => $request->email,
                "password" => $request->password,
            ]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
            ]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }
}
