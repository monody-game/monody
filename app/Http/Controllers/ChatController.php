<?php

namespace App\Http\Controllers;

use App\Models\Message;

class ChatController extends Controller
{
    public function all()
    {
        $messages = Message::select('*')->where('id', '=', 1)->get();
        dd($messages);
    }
}
