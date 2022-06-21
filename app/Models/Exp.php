<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exp extends Model
{
    use HasFactory;

    protected $table = 'exp';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'exp',
    ];
}
