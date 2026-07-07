<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    use HasFactory;

    protected $table = 'bots';
    protected $guarded = ['id']; // Semua kolom bisa diisi kecuali ID

    protected $casts = [
        'active_plugins' => 'array',
        'last_seen' => 'datetime'
    ];
}