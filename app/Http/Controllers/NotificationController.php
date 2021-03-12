<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    function store($buyer_id, $text) {
    	$notification = Notification::create([
    		'buyer_id' => $buyer_id,
    		'text'     => $text,
    	]);
    }
}
