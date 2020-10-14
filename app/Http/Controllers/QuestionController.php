<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Question;

class QuestionController extends Controller
{
    function index() {
    	$questions = Question::where('user_id', $this->userId())
    							->with('article.images')
    							->with('buyer')
    							->doesnthave('answer')
    							->get();
    	return response()->json(['questions' => $questions], 200);
    }

    function delete($id) {
    	$question = Question::find($id);
    	$question->delete();
    	return response(null, 200);
    }
}
