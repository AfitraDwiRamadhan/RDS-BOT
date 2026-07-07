<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $guarded = ['id'];

    // Relasi ke Bot
    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}
