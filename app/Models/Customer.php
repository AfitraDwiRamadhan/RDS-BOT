<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $guarded = ['id'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}