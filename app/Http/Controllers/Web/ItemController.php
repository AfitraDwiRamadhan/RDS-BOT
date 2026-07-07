<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Bot;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $botId = $request->query('bot_id');
        $bots = Bot::all();

        $query = Item::latest();

        if ($botId) {
            $query->where('bot_id', $botId);
        }

        $items = $query->get();
        return view('items.index', compact('items', 'bots', 'botId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:items,id',
            'bot_id' => 'nullable|exists:bots,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'bot_id' => $request->bot_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->id) {
            $item = Item::findOrFail($request->id);
            $item->update($data);
            $message = 'Item berhasil diperbarui!';
        } else {
            Item::create($data);
            $message = 'Item baru berhasil ditambahkan!';
        }

        return redirect()->route('items.index')->with('success', $message);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:items,id',
            'is_active' => 'required|boolean',
        ]);

        $item = Item::findOrFail($request->id);
        $item->update(['is_active' => $request->is_active]);

        return response()->json(['success' => true, 'message' => 'Status item berhasil diubah!']);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item berhasil dihapus dari daftar penjualan!');
    }
}
