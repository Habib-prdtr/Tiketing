<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterStoreRequest;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            if (!Auth::guard('web')->attempt(request()->only('email', 'password'))) {
                return response()->json(['massage' => 'Unauthorized', 'data' => null], 401);   
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['massage' => 'Login Berhasil', 'data' => ['user' => new UserResource($user), 'token' => $token]], 200);
        } catch (Exception $e) {
            return response()->json(['massage' => 'Terjadi Kesalahan', 'error' => $e->getMessage()], 500);
        }
    }

    public function me(){
        try {
            $user = Auth::user();
            return response()->json([
                'massage' => 'Profile User Berhasil diambil', 
                'data' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'massage' => 'Terjadi Kesalahan', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();
            return response()->json([
                'massage' => 'Logout Berhasil', 
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'massage' => 'Terjadi Kesalahan', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(RegisterStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = Hash::make($data['password']);
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'massage' => 'Registrasi Berhasil', 
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'massage' => 'Terjadi Kesalahan', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
