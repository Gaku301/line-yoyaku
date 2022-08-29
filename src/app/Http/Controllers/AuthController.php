<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Do something about auth
 */
class AuthController extends Controller
{
    /**
     * User sign up
     */
    public function regist(Request $request)
    {
        try {
            $user = new User;
            $user->fill($request->except('password'));
            $user->password = Hash::make($request->password);
            $user->save();

            $response = [
                'status' => 200,
                'reuslt' => ['user'=> $user]
            ];
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Regist user failed');
            Log::debug($e->getMessage());
            $response = [
                'status' => $e->getStatusCode(),
                'result' => ['user' => []]
            ];
        }

        return response()->json($response);
    }

    /**
     * User sign in
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // login処理
            if (Auth::attempt($credentials)) {
                $user = User::whereEmail($request->email)->first();
                $request->session()->regenerate();
                $response = [
                    'status' => 200,
                    'result' => ['user' => Auth::login($user)]
                ];
            } else {
                $response['status'] = 401;
            }
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Login failed');
            Log::debug($e->getMessage());
            $response = [
                'status' => $e->getStatusCode(),
                'result' => ['user' => []]
            ];
        }

        return response()->json($response);
    }

    /**
     * User sign out
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $response = [
                'status' => 200,
                'result' => ['is_logout' => true]
            ];
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Logout failed');
            Log::debug($e->getMessage());
            $response = [
                'status' => $e->getStatusCode(),
                'result' => ['is_logout' => false]
            ];
        }

        return response()->json($response);
    }
}
