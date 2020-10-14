<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use App\Configuration;
use App\Events\QuestionAnsweredEvent;

class AnswerController extends Controller
{
    function store(Request $request) {
    	$answer = Answer::create([
    		'text'		  => ucfirst($request->text),
    		'question_id' => $request->question_id
    	]);
    	$configuration = Configuration::where('buyer_id', $request->buyer_id)
    									->first();
    	$configuration->questions_seen = 0;
    	$configuration->save();
    	broadcast(new QuestionAnsweredEvent($answer));
    	return response(null, 201);
    }
}
