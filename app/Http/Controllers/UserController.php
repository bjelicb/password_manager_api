<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;


class UserController extends Controller
{
    private $auth;

    /**
     * Create a new UserController instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth->guard('sanctum');
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        try {
            $users = (Auth::user()->role === 'admin') ? User::all() : User::where('id', Auth::id())->get();
            return response()->json($users);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get details of a specific user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userDetails($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($this->checkUserRole($id)) {
                return response()->json($user);
            } else {
                return response()->json(['error' => 'Locked'], 403);
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($this->checkUserRole($id)) {
                $data = $request->except('password');
                $user->update($data);
                return response()->json($user, 200);
            } else {
                return response()->json(['error' => 'Locked'], 403);
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->role === 'admin') {
                return response()->json(['error' => 'Admin users cannot be deleted'], 403);
            }

            if (!$this->checkUserRole($id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $user->delete();
            
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Check user role.
     *
     * @param  int  $id
     * @return bool
     */
    private function checkUserRole($id)
    {
        if (Auth::user()->role === 'admin' || Auth::id() == $id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Handle exceptions.
     *
     * @param  \Exception  $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleException($e)
    {
        $message = ($e instanceof ModelNotFoundException) ? 'User not found' : $e->getMessage();
        $code = ($e instanceof ModelNotFoundException) ? 404 : ($e->getCode() ?: 500);

        return response()->json(['error' => 'Operation failed.', 'message' => $message], $code);
    }

    /**
     * Ping test comunication
     */
    public function ping()
    {
        return 'pong';
    }
}