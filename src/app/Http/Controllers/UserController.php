<?php

namespace App\Http\Controllers;

use App\Models\LineBot;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Save settings
     * ・ユーザーの設定情報を保存する
     */
    public function settings(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            if ($user->lineBot) {
                // 更新
                $lineBot = $user->lineBot;
                $lineBot->fill($request->except('user_id'));
            } else {
                // 新規登録
                $lineBot = new LineBot();
                $lineBot->fill($request->except('user_id'));
                $lineBot->user_id = $user->id;
            }
            $lineBot->save();

            $response = [
                'status' => 200,
                'result' => ['lineBot' => $lineBot]
            ];
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Save settings failed');
            Log::debug($e->getMessage());
            $response = [
                'status' => $e->getStatusCode(),
                'result' => false
            ];
        }

        return response()->json($response);
    }
}
