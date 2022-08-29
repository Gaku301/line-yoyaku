<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\RichMenuBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class LineController extends Controller
{
    /** @var CurlHTTPClient */
    private $htttpClient;
    /** @var LINEBot */
    private $bot;

    /**
     * @return void
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
     * Create rich menu for LINE Messaging API
     * ・リッチメニューを作成する
     */
    public function createRichMenu()
    {
        // Create rich menu
        $richMenuSizeBuilder = new RichMenuSizeBuilder(470, 1200);
        $richMenuAreaBuilder = [];
        // Reserve area
        $reserveRichMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 400, 470);
        $reserveTemplateActionBuilder = new MessageTemplateActionBuilder('reserve', '予約する');
        $richMenuAreaBuilder[] = new RichMenuAreaBuilder($reserveRichMenuAreaBoundsBuilder, $reserveTemplateActionBuilder);
        // Confirm area
        $confirmrRichMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 800, 470);
        $confirmTemplateActionBuilder = new MessageTemplateActionBuilder('confirm', '予約の確認');
        $richMenuAreaBuilder[] = new RichMenuAreaBuilder($confirmrRichMenuAreaBoundsBuilder, $confirmTemplateActionBuilder);
        // Card area
        $cardRichMenuAreaBoundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 1200, 470);
        $cardTemplateActionBuilder = new MessageTemplateActionBuilder('card', 'カードを表示する');
        $richMenuAreaBuilder[] = new RichMenuAreaBuilder($cardRichMenuAreaBoundsBuilder, $cardTemplateActionBuilder);
        $richMenuBuilder = new RichMenuBuilder($richMenuSizeBuilder, false, 'default', 'メニュー', $richMenuAreaBuilder);
        $response = $this->bot->createRichMenu($richMenuBuilder);
        if (!$response->isSucceeded()) {
            Log::debug('Create rich menu Failed');
            Log::debug('httpStatus: ' . $response->getHTTPStatus() . ' body: ' . $response->getRawBody());
        }

        // Upload rich menu image
        // "php artisan storage:link"を実行してpublic/storageにアクセスできるようにしておく
        $imagePath = 'storage/default.png'; // フルパスではなくimageのパスのみを指定
        $contentType = 'image/png';
        $richMenuId = $response->getJSONDecodedBody()['richMenuId'];
        $response = $this->bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType);
        if (!$response->isSucceeded()) {
            Log::debug('Upload rich menu image Failed');
            Log::debug('httpStatus: ' . $response->getHTTPStatus() . ' body: ' . $response->getRawBody());
        }

        // Set default rich menu
        $response = $this->bot->setDefaultRichMenuId($richMenuId);
        if (!$response->isSucceeded()) {
            Log::debug('Set default rich menu Failed');
            Log::debug('httpStatus: ' . $response->getHTTPStatus() . ' body: ' . $response->getRawBody());
        }

        echo 'Create success';
    }

    /**
     * Unset rich menu
     * ・作成したリッチメニューを非表示にする
     */
    public function unsetRichMenu()
    {
        // Unset default rich menu
        $response = $this->bot->cancelDefaultRichMenuId();
        if ($response->isSucceeded()) {
            Log::debug('Unset default rich menu Failed');
            Log::debug('httpStatus: ' . $response->getHTTPStatus() . 'body: ' . $response->getRawBody());
        }

        echo 'Unset success';
    }

    /**
     * Get LINE friends
     * ・公式LINEアカウントから友達情報を取得する
     */
    public function friends(Request $request)
    {
        try {
            $line_bot_id = '';
            $friends = Friend::where('line_bot_id', $line_bot_id)->get();

            $response = [
                'status' => 200,
                'result' => ['friends' => $friends]
            ];
        } catch (Exception $e) {
            Log::debug(__METHOD__ . 'Get LINE friends failed');
            Log::debug($e->getMessage());
            $response = [
                'status' => $e->getStatusCode(),
                'result' => ['friends' => []]
            ];
        }

        return response()->json($response);
    }
}
