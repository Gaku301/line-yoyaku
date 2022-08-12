<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\SignatureValidator;

class LineWebhookController extends Controller
{
    // webhook event type(この中にあるタイプのみ処理を行う)
    const EVENT_TYPES = ['message', 'follow', 'unfollow'];

    /**
     * Callback from LINE Messaging API(webhook)
     * @param Request $request
     */
    public function webhook(Request $request)
    {
        $htttpClient = new CurlHTTPClient(config('line.channel_access_token'));
        $bot = new LINEBot($htttpClient, ['channelSecret' => config('line.channel_secret')]);

        $signature = $request->headers->get(HTTPHeader::LINE_SIGNATURE);
        if (!SignatureValidator::validateSignature($request->getContent(), config('line.channel_secret'), $signature)) {
            abort(400);
        }

        // webhookイベントオブジェクトを取得
        $events = $bot->parseEventRequest($request->getContent(), $signature);
        // webhookのイベントが複数で送信されてくる可能性があるのでforeachで回す
        foreach ($events as $event) {
            // イベントタイプを取得
            $type = $event->getType();
            if (in_array($type, self::EVENT_TYPES)) {
                // 対象のイベントタイプがある場合は処理を実行
                $this->$type();
            }
        }
    }

    /**
     * Got messages from LINE
     */
    public function message()
    {
        $hoge = 'message';
    }

    /**
     * Got follow or unblock event from LINE
     */
    public function follow()
    {
        $hoge = 'follow';
    }

    /**
     * Got block event from LINE
     */
    public function unfollow()
    {
        $hoge = 'unfollow';
    }
}
