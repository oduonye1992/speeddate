<?php

namespace App\Http\Controllers;

use App\Profile;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class AuthenticateController extends Controller
{
    public function login(Request $request, JWTAuth $jw){
        \Log::info('Request to login. cradentials '.json_encode($request->all()));
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($aa = $validator->fails()) {
            return response($validator->errors(), 500);
        }
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        try {
            // verify the credentials and create a token for the user
            if (! $token = $jw->attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
            $user = $jw->authenticate($token);
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        // if no errors are encountered we can return a JWT
        // Get user profile
        $profile = Profile::where('user_id', $user->id)->get()[0];
        $data = [
            'user' => $user,
            'extra' => compact('token'),
            'profile' => $profile
        ];
        return response()->json($data);
    }
    public function register(Request $request, JWTAuth $jw){
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'name' => 'required'
        ]);
        if ($aa = $validator->fails()) {
            return response($validator->errors(), 500);
        }
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        $request['password'] = Hash::make($request->password);
        $user = User::create($request->all());
        // Create the profile
        $profileData = [
            'user_id' => $user->id
        ];
        $profile = Profile::create($profileData);
        try {
            // verify the credentials and create a token for the user
            if (!$token = $jw->attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token', 'message' => $e->getMessage()], 500);
        }
        // if no errors are encountered we can return a JWT
        $data = [
            'user' => $user,
            'extra' => compact('token'),
            'profile' => $profile
        ];
        return response()->json($data);
    }
}
