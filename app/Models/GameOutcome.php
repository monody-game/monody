<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameOutcome extends Model
{
    use HasFactory;

    protected $table = 'game_outcome';

    public $timestamps = false;

    protected $fillable = ['user_id', 'role_id', 'win'];
}
