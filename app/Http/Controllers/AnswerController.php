<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Buyer;
use App\Events\QuestionAnswered as QuestionAnsweredEvent;
use App\Http\Controllers\Helpers\MessageHelper;
use App\Notifications\QuestionAnswered as QuestionAnsweredNotification;
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
        MessageHelper::sendQuestionAnsweredMessage($question);
        // $buyer = Buyer::find($question->buyer_id);
        // $buyer->notify(new QuestionAnsweredNotification($question));
        // event(new QuestionAnsweredEvent($question));
    	return response(null, 201);
    }
}
