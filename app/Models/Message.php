<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $gameId
 * @property array  $author
 * @property string $content
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = ['gameId', 'author', 'content'];
}
