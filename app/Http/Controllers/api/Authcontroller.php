<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\userresource;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Authcontroller extends Controller
{
    //registration function
    public function register(Request $request): Response
    {
        //validate inputs
        try {
            $request->validate([
                'name'=> 'required|string|max:255',
                'email'=> 'required|email|max:255|unique:users,email',
                'password' => 'required|min:6|max:255', 
            ]);
        } catch (ValidationException $e) {
            return response([
                'message' => __('app.registr_valid_erreurs'),
                'errors' => $e->errors()
            ]);
        }

        // Create user
        $user = User::create([
            'uuid' =>Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' =>Hash::make($request->password)
        ]);
              // Create token
        $token = $user->createToken('auth')->plainTextToken;

        // Return response
        return response([
              'message' => $user->email_verified_at ? __('app.registration_success'):__('app.registration_verify'),
            'result' => [
                'user' => new userresource($user),
                'token' => $token
            ]
            ],201);
    }

        //login function
    public function login(Request $request): Response
    {
        //validate inputs
        try {
            $request->validate([
                'email'=> 'required|email|max:255',
                'password' => 'required|min:6|max:255', 
            ]);
        } catch (ValidationException $e) {
            return response([
                'message' => __('app.registr_valid_erreurs'),
                'errors' => $e->errors()
            ]);
        }

        // login user
        $user = User::where('email',$request->email)->first();
        // Check if user exists and password is correct
        if(!$user){
            return response([
                    'message'=> __('auth.failed'),
            ],401);
        } else if(!Hash::check($request->password,$user->password)){
            return response([
                    'message'=> __('auth.password'),
            ],401);
        }

        // Authentication successful, create token
        $token = $user->createToken('auth')->plainTextToken;
        // Return response
        return response([
            'message' => $user->email_verified_at ? __('app.login_success'):__('app.login_success_verify'),
            'result' => [
                'user' => new userresource($user),
                'token' => $token
            ]
        ]);
    }


}
