<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Account;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AccountController extends Controller
{
    private $auth;

    /**
     * Initialize UserController.
     *
     * Applies JWT authentication middleware to all controller actions to ensure
     * that only authenticated users can access them. The 'auth:api' middleware
     * leverages JWT (JSON Web Tokens) for user authentication checks.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Handle exceptions.
     *
     * @param  \Exception  $e
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleException($e)
    {
        if ($e instanceof ModelNotFoundException && $e->getModel() == Account::class) {
            return response()->json(['error' => 'Account does not exist'], 404);
        }

        return response()->json(['error' => 'Operation failed.', 'message' => $e->getMessage()], 500);
    }

    /**
     * Check if user can access the account.
     *
     * @param  int  $accountId
     * @return \App\Models\Account
     * @throws \Exception
     */
    private function checkIfUserCanAccess($accountId)
    {   
        $account = Account::findOrFail($accountId);
        
        if (Auth::user()->role !== 'admin' && Auth::user()->id !== $account->user_id) {
            throw new Exception('You do not have permission to access this account.', 401);
        }
        
        return $account;
    }

    /**
     * Get all accounts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccounts()
    {
        try {
            $userRole = Auth::user()->role;
            $accounts = ($userRole === 'admin') ? Account::all() : Account::where('user_id', Auth::user()->id)->get();

            if ($accounts->isEmpty()) {
                throw new Exception('No accounts found.', 404);
            }

            $accounts = $accounts->map(function ($account) {
                $account->password = decrypt($account->password);
                return $account;
            });

            return response()->json($accounts);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get details of a specific account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountDetails($id)
    {
        try {
            $account = $this->checkIfUserCanAccess($id);
            $account->password = decrypt($account->password);
            return response()->json($account);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Add a new account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAccount(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $encryptPassword = encrypt($request->password);
            $accountData = $request->except(['password', 'password_confirmation']);
            $accountData['password'] = $encryptPassword;
            $accountData['user_id'] = Auth::user()->id;

            $account = Account::create($accountData);

            return response()->json($account, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update an existing account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccount(Request $request, $id)
    {
        try {

            $account = $this->checkIfUserCanAccess($id);

            $data = $request->except('password');
            $account->update($data);

            $account->password = decrypt($account->password);

            return response()->json($account, 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Reset password for an account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, $id)
    {
        try {
            
            $account = $this->checkIfUserCanAccess($id);

            $request->validate([
                'password' => 'required|string|min:6|confirmed'
            ]);

            $currentPasswordDecrypted = decrypt($account->password);

            if ($request->password === $currentPasswordDecrypted) {
                throw new Exception('New password must be different from the current password.', 422);
            }

            $account->update([
                'password' => encrypt($request->password),
            ]);

            return response()->json(['message' => 'Password reset successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()->first()], 422);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete an account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount($id)
    {
        try {
            $account = $this->checkIfUserCanAccess($id);
            $account->delete();
            return response()->json(['message' => 'Account successfully deleted'], 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
