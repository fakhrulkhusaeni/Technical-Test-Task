<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameOrder extends Model
{
    protected $fillable = [
        'user_id',
        'game_code',
        'reference_id',
        'amount',
        'status'
    ];
}
