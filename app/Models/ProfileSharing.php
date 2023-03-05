<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileSharing extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'created_at'];

    protected $table = 'profile_sharings';
}
