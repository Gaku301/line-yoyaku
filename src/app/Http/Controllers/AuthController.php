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
        $response = [
            'status' => 400,
            'result' => ['user' => []]
        ];
        try {
            $user = new User;
            $user->fill($request->except('password'));
            $user->password = Hash::make($request->password);
            $user->save();

            $response['status'] = 200;
            $response['result']['user'] = $user;
            $response['result']['access_token'] = $user->createToken('auth_token')->plainTextToken;
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Regist user failed');
            Log::debug($e->getMessage());
            $response['status'] = $e->getStatusCode();
        }

        return response()->json($response);
    }

    /**
     * User sign in
     */
    public function login(Request $request)
    {
        $response = [
            'status' => 400,
            'result' => ['user' => []]
        ];

        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // login処理
            if (Auth::attempt($credentials)) {
                $user = User::whereEmail($request->email)->first();
                $request->session()->regenerate();
                $response['status'] = 200;
                $response['result']['user'] = $user;
                $response['result']['access_token'] = $user->createToken('auth_token')->plainTextToken;
            } else {
                $response['status'] = 401;
            }
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Login failed');
            Log::debug($e->getMessage());
            $response['status'] = $e->getStatusCode();
        }

        return response()->json($response);
    }
}
