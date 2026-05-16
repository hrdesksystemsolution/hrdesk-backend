<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422)
                ->header('Content-Type', 'application/json');
        }

        $credentials = $request->only('username', 'password');

        try {
            // Find user by username
            $user = User::where('username', $credentials['username'])->first();

            // Check if user exists and password matches 
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid username or password'
                ], 401)->header('Content-Type', 'application/json');
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'emailid' => $user->emailid,
                    'fullname' => $user->fullname,
                    'mnuaccess' => $user->mnuaccess,
                    'dashboard' => $user->dashboard,
                    'dashboard_access' => $user->dashboard_access,
                ]
            ], 200)->header('Content-Type', 'application/json');
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return response()->json([
            'success' => true,
            'token' => $token
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
