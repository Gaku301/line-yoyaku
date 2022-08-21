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
    public function regist(Request $request)
    {
        $response = [
            'statusCode' => 400,
            'result' => ['user' => []]
        ];
        try {
            $user = new User;
            $user->fill($request->except('password'));
            $user->password = Hash::make($request->password);
            $user->save();

            Auth::login($user, true);
            $response['statusCode'] = 200;
            $response['result']['user'] = $user;
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Regist user failed');
            Log::debug($e->getMessage());
            $response['statusCode'] = $e->getStatusCode();
        }

        return response()->json($response);
    }
}
