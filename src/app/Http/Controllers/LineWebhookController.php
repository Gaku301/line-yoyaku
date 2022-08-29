<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;

class LineWebhookController extends Controller
{
    // webhook event type(この中にあるタイプのみ処理を行う)
    const EVENT_TYPES = ['message', 'follow', 'unfollow'];

    /** @var CurlHTTPClient */
    private $htttpClient;
    /** @var LINEBot */
    private $bot;

    /** 
     *  @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // LINEBotの初期化処理
            $this->htttpClient = new CurlHTTPClient(config('line.channel_access_token'));
            $this->bot = new LINEBot($this->htttpClient, ['channelSecret' => config('line.channel_secret')]);

            return $next($request);
        });
    }

    /**
     * Callback from LINE Messaging API(webhook)
     * ・LINEからwebhookイベントを受け取る
     * @param Request $request
     */
    public function webhook(Request $request)
    {
        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
        if (!SignatureValidator::validateSignature($request->getContent(), config('line.channel_secret'), $signature)) {
            abort(400);
        }

        // webhookイベントオブジェクトを取得
        $events = $this->bot->parseEventRequest($request->getContent(), $signature);
        // webhookのイベントが複数で送信されてくる可能性があるのでforeachで回す
        foreach ($events as $event) {
            // イベントタイプを取得
            $type = $event->getType();
            $userId = $event->getUserId();
            if (in_array($type, self::EVENT_TYPES)) {
                // 対象のイベントタイプがある場合は処理を実行
                $this->$type($userId);
                continue;
            }
        }
    }

    /**
     * Got messages from LINE
     * ・メッセージ受信イベントを処理
     * @param string $userId LINE側のユーザーID
     */
    public function message($userId)
    {
        $hoge = 'message';
    }

    /**
     * Got follow or unblock event from LINE
     * ・友達追加イベントを処理
     * @param string $userId LINE側のユーザーID
     */
    public function follow($userId)
    {
        if (empty($userId)) {
            throw new Exception();
        }

        $friend = Friend::where('line_user_id', $userId)->first();
        if (!empty($friend) && $friend->is_blocked == true) {
            // 対象のFriendが存在すればブロックステータスを変更する
            try {
                $friend->is_blocked = false;
                $friend->save();
            } catch (Exception $e) {
                Log::debug(__METHOD__ . 'Update friend from follow event failed');
                Log::debug($e->getMessage());
            }
        } else {
            // 対象のFriendがいなければ保存
            try {
                // LINE側のユーザー情報を取得
                $profile = $this->getProfile($userId);

                $friend = new Friend();
                $friend->fill([
                    'line_bot_id' => '', // TODO: LineBotIdをセット
                    'line_user_id' => $userId,
                    'line_display_name' => $profile['displayName'],
                    'line_icon_url' => $profile['pictureUrl'] ?? NULL
                ]);
                $friend->save();
            } catch (Exception $e) {
                Log::debug(__METHOD__ . 'Create friend from follow event failed');
                Log::debug($e->getMessage());
            }
        }
    }

    /**
     * Got block event from LINE
     * ・ブロック通知イベントを処理
     * @param string $userId LINE側のユーザーID
     */
    public function unfollow($userId)
    {
        $hoge = 'unfollow';
    }

    /**
     * Get profile from LINE 
     * ・LINE側のユーザー情報を取得する
     * @param string $userId LINE側のユーザーID
     * @return array
     */
    public function getProfile($userId)
    {
        $profile = [];
        try {
            $response = $this->bot->getProfile($userId);
            if (!$response->isSucceeded()) {
                throw new Exception(
                    'httpStatus: ' . $response->getHTTPStatus() . 
                    ' body: ' . $response->getRawBody()
                );
            }
            $profile = $response->getJSONDecodedBody();
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Get user profile from LINE failed');
            Log::debug($e->getMessage());
        }

        return $profile;
    }
}
