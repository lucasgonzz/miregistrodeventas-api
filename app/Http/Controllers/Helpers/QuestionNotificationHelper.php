<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use Carbon\Carbon;

class QuestionNotificationHelper {

	static function getQuestionAnsweredMessage($question) {
        return "Respondimos a tu pregunta de ".$question->article->name;
	}

}