<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    /*protected function guard()
    {
        return auth()->guard('admins');
    }*/



    public function login(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $admin = Auth::user();
            $token = $admin->createToken('maker-checker')->plainTextToken;

            return response()->json(
                [
                    'token' => $token,
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                    ]
                ], 200);
        } else {
            return response()->json(['message' => 'Invalid Email/Password combination'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
