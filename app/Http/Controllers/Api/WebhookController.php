<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bot;
use App\Modules\MessageRouter;

class WebhookController extends Controller
{
    private $secret;

    public function __construct()
    {
        $this->secret = config('rdsbot.webhook_secret');
    }

    public function updateStatus(Request $request)
    {
        if ($request->input('secret') !== $this->secret) return response()->json([], 401);

        $botId = $request->input('bot_id');
        $status = $request->input('status');

        Bot::updateOrCreate(
            ['bot_id' => $botId],
            [
                'name' => $botId,
                'phone_number' => $botId, // Nilai fallback
                'status' => $status,
                'last_seen' => now(),
                'active_plugins' => ['order']
            ]
        );
        return response()->json(['success' => true]);
    }

    public function receiveMessage(Request $request)
    {
        if ($request->input('secret') !== $this->secret) return response()->json([], 401);

        $botId = $request->input('botId');
        $jid = $request->input('jid');
        $sender = $request->input('sender');
        $text = $request->input('text');
        $pushName = $request->input('pushName') ?? 'User';
        $quotedText = $request->input('quotedText') ?? '';
        $mediaPath = $request->input('mediaPath');

        // Lempar pesan beserta Quoted Text, Sender, dan Media Path ke Router
        MessageRouter::handle($botId, $jid, $sender, $text, $pushName, $quotedText, $mediaPath);

        return response()->json(['success' => true]);
    }
}