<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], $e->getStatusCode());
        }

        $user = $this->jwt->user();
        $tokenExpiration = \Carbon\Carbon::now('Asia/Jakarta')->addHours(2)->timestamp;

        return response()->json([
          'message' => 'Login Successful',
          'data' => compact('user','token', 'tokenExpiration'),
        ], 200);
    }

    public function logout()
    {
        try {
            $this->jwt->invalidate($this->jwt->getToken()->get());
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json([
          'message' => 'Logout Successful',
          'data' => $this->jwt->getToken(),
        ], 200);
    }

    public function authenticatedUser() {
      return response()->json([
        'message' => 'user authenticated',
        'data' => [
          'user' => $this->jwt->user(),
        ]
      ], 200);
    }
}
