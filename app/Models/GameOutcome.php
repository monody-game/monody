<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameOutcome extends Model
{
    use HasFactory;

    protected $table = 'game_outcome';

    public $timestamps = false;

	protected $casts = [
		'winning_role' => Role::class
	];

    protected $fillable = ['user_id', 'role_id', 'win', 'winning_role', 'round'];
}
