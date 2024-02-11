<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Custom messages for validation errors
        $customMessages = ['email.unique' => 'Email already exists!'];

        // Validate input
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ], $customMessages);

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Return response
        return response()->json(['message' => 'Successful registration'], 201);
    }

    /**
     * Log in a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['message' => 'Successful login', 'token' => $token], 200);
    }

    /**
     * Log out a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * Change the password for the current authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|confirmed',
            ]);

            // Check if new password and confirmation match
            if ($request->password !== $request->password_confirmation) {
                throw new Exception('Passwords do not match', 422);
            }

            // Check if current password is correct
            if (!Hash::check($request->current_password, Auth::user()->password)) {
                throw new Exception('Current password is incorrect', 401);
            }

            // Change password
            Auth::user()->update([
                'password' => Hash::make($request->password),
            ]);

            // Return response
            return response()->json(['message' => 'Password changed successfully'], 200);
        } catch (Exception $e) {
            // Check if there is a status code in the exception
            $statusCode = $e->getCode() ?: 500;
            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    /**
     * Change the password for a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserPassword(Request $request, $id)
    {
        try {
            // Find user by ID
            $user = User::findOrFail($id);

            // Check if user is admin
            if (Auth::user()->role !== 'admin') {
                throw new Exception('Unauthorized', 401);
            }

            // Validate input
            $request->validate([
                'password' => 'required|string|confirmed',
            ]);

            // Change password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Return response
            return response()->json(['message' => 'Password changed successfully for user ID: '.$id], 200);
        } catch (Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }
}
