<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Buyer;
use App\Events\QuestionAnsweredEvent;
use App\Http\Controllers\NotificationController;
use App\Notifications\QuestionAnswered;
use App\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    function store(Request $request) {
    	$answer = Answer::create([
    		'text'		  => ucfirst($request->text),
    		'question_id' => $request->question_id
    	]);
        $question = Question::where('id', $request->question_id)
                            ->with('article')
                            ->first();
        $buyer = Buyer::find($question->buyer_id);
        $buyer->notify(new QuestionAnswered($question));
    	// broadcast(new QuestionAnsweredEvent($answer));
    	return response(null, 201);
    }
}
