<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';
    protected $guarded = ['id'];

    public function bot()
    {
        return $this->belongsTo(Bot::class, 'bot_id');
    }
}
