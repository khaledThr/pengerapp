<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\userresource;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\Otpmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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
          //send verification code
            if(!$user->email_verified_at){
                $this->otp($user);}
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
            //send verification code
            if(!$user->email_verified_at){
                $this->otp($user);}
        // Return response
        return response([
            'message' => $user->email_verified_at ? __('app.login_success'):__('app.login_success_verify'),
            'result' => [
                'user' => new userresource($user),
                'token' => $token
            ]
        ]);
    }

   public function otp(User $user): Response
    {
        // generate otp
        $otp = Otp::create([
            'user_id' => $user->id,
            'type' => 'auth', 
            'code' => random_int(100000,999999),
            'active' => 1
        ]);
        // send mail
        Mail::to($user->email)->send(new Otpmail($otp, $user));
        // return response
        return response([
            'message' => __('app.otp_sent'),
        ]);
}

//OTP verification
   public function verify(Request $request): Response
    { 
        //verify request
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);
        //get user
        $authUser = Auth::user();
        if(!$authUser) {
            return response([
                'message' => __('auth.unauthenticated'),
            ], 401);
        }
        // Retrieve the user model from the database
        $user = User::find($authUser->id);
        //chech otp
        $otp = Otp::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('active', 1)
            ->first();
            if(!$otp) {
                return response([
                    'message' => __('app.otp_invalid'),
                ], 422);
            }else{
                //update  user
                $user->email_verified_at = Carbon::now();
                $user->save();
                //disactivate otp
                $otp->active=0;
                $otp->update();
                return response([
                    'message'=>__('app.validation_sucess'),
                    'user' => new userresource($user)
                ]);
            }
        }}
        //password reset otp

