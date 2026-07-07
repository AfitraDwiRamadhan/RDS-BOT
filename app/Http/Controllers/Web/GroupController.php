<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Bot;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $botId = $request->query('bot_id');
        $bots = Bot::all();

        $query = Group::with('bot')->latest();

        if ($botId) {
            $query->where('bot_id', $botId);
        }

        $groups = $query->get();
        return view('groups.index', compact('groups', 'bots', 'botId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bot_id' => 'required|exists:bots,id',
            'group_jid' => 'required|string',
            'group_name' => 'required|string',
            'type' => 'required|in:buyer,seller,monitoring,general',
        ]);

        // Clean group JID format if needed
        $groupJid = trim($request->group_jid);
        if (!str_contains($groupJid, '@g.us')) {
            $groupJid .= '@g.us';
        }

        Group::updateOrCreate(
            ['bot_id' => $request->bot_id, 'group_jid' => $groupJid],
            ['group_name' => $request->group_name, 'type' => $request->type]
        );

        return redirect()->route('groups.index')->with('success', 'Grup berhasil disimpan/diperbarui!');
    }

    public function updateType(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'type' => 'required|in:buyer,seller,monitoring,general',
        ]);

        $group = Group::findOrFail($request->group_id);
        $group->update(['type' => $request->type]);

        return response()->json(['success' => true, 'message' => 'Tipe grup berhasil diperbarui!']);
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grup berhasil dihapus dari sistem!');
    }
}
