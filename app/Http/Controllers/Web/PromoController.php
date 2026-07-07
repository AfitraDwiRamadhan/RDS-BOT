<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;
use App\Models\Bot;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        $botId = $request->query('bot_id');
        $bots = Bot::all();

        $query = Promo::latest();

        if ($botId) {
            $query->where('bot_id', $botId);
        }

        $promos = $query->get();
        return view('promos.index', compact('promos', 'bots', 'botId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:promos,id',
            'bot_id' => 'nullable|exists:bots,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'bot_id' => $request->bot_id,
            'title' => $request->title,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/promos'), $fileName);
            $data['image_path'] = '/uploads/promos/' . $fileName;
        }

        if ($request->id) {
            $promo = Promo::findOrFail($request->id);
            $promo->update($data);
            $message = 'Promo/Event berhasil diperbarui!';
        } else {
            Promo::create($data);
            $message = 'Promo/Event baru berhasil ditambahkan!';
        }

        return redirect()->route('promos.index')->with('success', $message);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:promos,id',
            'is_active' => 'required|boolean',
        ]);

        $promo = Promo::findOrFail($request->id);
        $promo->update(['is_active' => $request->is_active]);

        return response()->json(['success' => true, 'message' => 'Status Promo/Event berhasil diubah!']);
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('promos.index')->with('success', 'Promo/Event berhasil dihapus!');
    }
}
