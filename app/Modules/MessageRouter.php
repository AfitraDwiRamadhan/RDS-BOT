<?php

namespace App\Modules;

use Illuminate\Support\Facades\Http;
use App\Plugins\Order\OrderPlugin;

class MessageRouter
{
    public static function handle($botId, $jid, $sender, $text, $pushName, $quotedText = '', $mediaPath = null)
    {
        $text = trim($text);
        if (empty($text)) return;

        $plugins = [
            new OrderPlugin(),
        ];

        foreach ($plugins as $plugin) {
            foreach ($plugin->getCommands() as $trigger) {
                if (str_starts_with(strtolower($text), strtolower($trigger))) {
                    $plugin->process($botId, $jid, $sender, $text, $pushName, $quotedText, $mediaPath);
                    return; 
                }
            }
        }
    }

    public static function sendMessage($botId, $jid, $text, array $mentions = [], $imageUrl = null)
    {
        $nodeUrl = config('rdsbot.node_server_url') . '/api/messages/send';
        try {
            Http::post($nodeUrl, [
                'botId' => $botId,
                'jid' => $jid,
                'text' => $text,
                'mentions' => $mentions,
                'imageUrl' => $imageUrl
            ]);
        } catch (\Exception $e) {}
    }
}