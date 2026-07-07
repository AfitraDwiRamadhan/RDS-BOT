<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Bot;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $botId = $request->query('bot_id');
        $bots = Bot::all();

        $query = Customer::with('orders.bot')->latest();

        if ($botId) {
            $query->whereHas('orders', function ($q) use ($botId) {
                $q->where('bot_id', $botId);
            });
        }

        $customers = $query->get();
        return view('customers.index', compact('customers', 'bots', 'botId'));
    }
}