<?php

namespace App\Plugins;

interface PluginInterface
{
    public function getCommands(): array;

    // Tambahkan $sender, $quotedText, & $mediaPath di parameter
    public function process($botId, $jid, $sender, $text, $pushName, $quotedText = '', $mediaPath = null);
}