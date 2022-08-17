<?php
/**
 * LINEに関する設定
 */

return [
    // Messaging API Channel secret
    'channel_secret' => env('LINE_CHANNEL_SECRET'),
    // Messaging API Channel access token
    'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN')
];