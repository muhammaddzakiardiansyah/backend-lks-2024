<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AtuhController extends Controller
{
    // login function
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|min:5",
        ]);
        if($validate->fails()) {
            return response()->json([
                "message" => "Invalid field",
                "errors" => $validate->errors(),
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if(!$user || Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email or Password incorret',
            ], 401);
        }
        $token = $user->createToken('accessToken')->plainTextToken;
        return response()->json([
            'message' => 'Login success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'accessToken' => $token,
            ]
            ], 200);
    }

    // logout function
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout success'
        ], 200);
    }
}
