<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'id',
        'owner_id',
        'users',
        'roles',
        'created_at',
        'is_started'
    ];
}
