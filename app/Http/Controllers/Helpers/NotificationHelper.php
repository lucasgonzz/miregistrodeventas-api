<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Helpers\UserHelper;
use App\Notifications\UpdatedArticle;
use App\User;
use Illuminate\Support\Facades\Notification;

class NotificationHelper {

	static function updatedArticle($article) {
		$owner_id = UserHelper::userId();
		$users = User::where('id', $owner_id)
						->orWhere('owner_id', $owner_id)
						->get();
		Notification::send($users, new UpdatedArticle($article));
	}
	
}