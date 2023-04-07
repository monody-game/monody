<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Elo extends Model
{
    protected $table = 'elo';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'elo',
    ];
}
