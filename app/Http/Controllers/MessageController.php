<?php

namespace App\Http\Controllers;

use App\Buyer;
use App\Http\Controllers\Helpers\StringHelper;
use App\Http\Controllers\Helpers\TwilioHelper;
use App\Message;
use App\Notifications\MessageSend;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    function fromBuyer($buyer_id) {
        $messages = Message::where('buyer_id', $buyer_id)
                            ->get();
        return response()->json(['messages' => $messages], 200);
    }

    function setRead($buyer_id) {
        $messages = Message::where('buyer_id', $buyer_id)
                            ->where('read', 0)
                            ->where('from_buyer', 1)
                            ->get();
        foreach ($messages as $message) {
            $message->read = 1;
            $message->save();
        }
        return response(null, 200);
    }

    function store(Request $request) {
        $message = Message::create([
            'user_id' => $this->userId(),
            'buyer_id' => $request->buyer_id,
            'text' => StringHelper::onlyFirstWordUpperCase($request->text),
        ]);
        $buyer = Buyer::find($request->buyer_id);
        $buyer->notify(new MessageSend($message));
        $title = 'Nuevo mensaje';
        TwilioHelper::sendNotification($message->buyer_id, $title, $message->text);
        return response()->json(['message' => $message], 201);
    }
}
