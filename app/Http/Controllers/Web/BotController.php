<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bot;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class BotController extends Controller
{
    public function dashboard()
    {
        $totalBots = Bot::count();
        $activeBots = Bot::where('status', 'active')->count();
        $offlineBots = Bot::where('status', 'inactive')->count();
        
        return view('dashboard', compact('totalBots', 'activeBots', 'offlineBots'));
    }

    public function index()
    {
        $bots = Bot::latest()->get();
        return view('bots.index', compact('bots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bot_id' => 'required|unique:bots',
            'name' => 'required',
            'phone_number' => 'required|unique:bots',
        ]);

        Bot::create([
            'bot_id' => $request->bot_id,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'status' => 'inactive',
            'active_plugins' => []
        ]);

        return redirect()->route('bots.index')->with('success', 'Bot berhasil ditambahkan!');
    }

    public function destroy(Bot $bot)
    {
        // 1. Beri tahu Node.js untuk mematikan engine bot ini secara realtime
        try {
            Http::post(config('rdsbot.node_server_url') . '/api/bots/stop', [
                'botId' => $bot->bot_id
            ]);
        } catch (\Exception $e) {
            // Abaikan jika server Node.js sedang mati
        }

        // 2. Hapus folder sesi secara fisik agar tidak menjadi Ghost Bot
        $sessionPath = storage_path('sessions/' . $bot->bot_id);
        if (File::exists($sessionPath)) {
            File::deleteDirectory($sessionPath);
        }

        // 3. Hapus data bot dari MySQL
        $bot->delete();

        return redirect()->route('bots.index')->with('success', 'Bot dan sesi sistem berhasil dihapus permanen!');
    }

    public function updateTemplate(Request $request, Bot $bot)
    {
        $request->validate([
            'form_template' => 'nullable|string'
        ]);

        $bot->update([
            'form_template' => $request->form_template
        ]);

        return redirect()->route('bots.index')->with('success', 'Template form untuk bot ' . $bot->bot_id . ' berhasil diperbarui!');
    }
}