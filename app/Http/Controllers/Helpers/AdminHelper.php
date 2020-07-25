<?php

namespace App\Http\Controllers\Helpers;

use App\Article;
use Carbon\Carbon;

class AdminHelper {
	static function setFinishDate($users) {
		foreach ($users as $user) {
			$user->finish_date = Carbon::parse($user->updated_at)->addMonths(1);
		}
		return $users;
	}
}