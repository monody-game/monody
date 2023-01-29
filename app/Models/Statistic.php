<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $table = 'statistics';

    protected $primaryKey = 'user_id';

    use HasUuids;

    public $timestamps = false;
}
