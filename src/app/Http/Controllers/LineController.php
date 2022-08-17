<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\RichMenuBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class LineController extends Controller
{
    /**
     * Create rich menu for LINE Messaging API
     */
    public function createRichMenu()
    {
        $htttpClient = new CurlHTTPClient(config('line.channel_access_token'));
        $bot = new LINEBot($htttpClient, ['channelSecret' => config('line.channel_secret')]);

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
        $response = $bot->createRichMenu($richMenuBuilder);
        if (!$response->isSucceeded()) {
            Log::debug('Create rich menu Failed');
            Log::debug('httpStatus: '.$response->getHTTPStatus().' body: '. $response->getRawBody());
        }

        // Upload rich menu image
        // "php artisan storage:link"を実行してpublic/storageにアクセスできるようにしておく
        $imagePath = 'storage/default.png'; // フルパスではなくimageのパスのみを指定
        $contentType = 'image/png';
        $richMenuId = $response->getJSONDecodedBody()['richMenuId'];
        $response = $bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType);
        if (!$response->isSucceeded()) {
            Log::debug('Upload rich menu image Failed');
            Log::debug('httpStatus: '.$response->getHTTPStatus().' body: '. $response->getRawBody());
        }

        // Set default rich menu
        $response = $bot->setDefaultRichMenuId($richMenuId);
        if (!$response->isSucceeded()) {
            Log::debug('Set default rich menu Failed');
            Log::debug('httpStatus: '.$response->getHTTPStatus().' body: '. $response->getRawBody());
        }

        echo 'success';
    }
}
