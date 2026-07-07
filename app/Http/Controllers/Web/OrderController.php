<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Bot;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $botId = $request->query('bot_id');
        $bots = Bot::all();

        $query = Order::with('customer')->latest();

        if ($botId) {
            $query->where('bot_id', $botId);
        }

        $orders = $query->get();
        
        return view('orders.index', compact('orders', 'bots', 'botId'));
    }
}