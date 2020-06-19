<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'Email' => 'required|email',
            'password' => 'required|min:5',
        ]);
        if($validate->fails())
        {
            return response()->json($validate->errors());
        }
        $credentials = $request->only('Email', 'password');

        if ($token = JWTAuth::attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'Fname' => 'required',
            'Lname' => 'required',
            'Email' => 'required|email|unique:users',
            'password' => 'required|min:5',
            'image' => 'required|image|mimes:png',
        ]);
        if($validate->fails())
        {
            return response()->json($validate->errors());
        }
        $user = new User;
        $user['F-name'] = $request['Fname'];
        $user['L-name'] = $request['Lname'];
        $user['email'] = $request['Email'];
        $user['image'] = $request['image'];
        $user['password'] = Hash::make($request['password']);
        $user->save();
       $token = JWTAuth::fromUser($user);
      return response()->json(compact('token'));
        
    }
    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
