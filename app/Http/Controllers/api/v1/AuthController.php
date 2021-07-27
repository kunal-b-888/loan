<?php

namespace App\Http\Controllers\api\v1;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\v1\ApiController;
use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $msg = "Saved Successfully!!";
        $rules = [
            'full_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            //dd('fail');
            return $this->sendError($validator->errors()->first(), 422);
        }
        // if validation passes, create the user
        //dd($validator);
        $user = new User;
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->signup_role = $request->signup_role;
        $user->signup_looking_for = $request->signup_looking_for;
        $user->heard_through = $request->heard_through;
        $user->api_token = Str::random(60);

        if ($response = $user->save()) {
            $response = $this->sendResponse($user, $msg);
        } else {
            $response = $this->sendError($response);
        }
        return $response;

    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            //dd('fail');
            return $this->sendError($validator->errors()->first(), 422);
        }
        $user = User::where('email', $request->email)->first();
        //dd($user);
        if ($user) {
	        if (Hash::check($request->password, $user->password)) {
	            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                //dd($token);
	            //$response = ['token' => $token];
	            $response = ['message' => 'You have been successfully logged in!', 'status'=> true,'token' => $token];
	    		return response($response, 200);
	        } else {
	            $response = ["message" => "Password mismatch", 'status'=> false];
	            return response($response, 422);
	        }
	    } else {
	        $response = ["message" =>'User does not exist', 'status'=> false];
	        return response($response, 422);
	    }
    }
}
