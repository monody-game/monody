<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';

    protected $fillable = ['user_id', 'badge_id', 'level', 'obtained_at'];

    public $timestamps = false;
}
