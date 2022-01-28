<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Title;

class TitleController extends Controller
{
    function index() {
    	$title = Title::where('user_id', $this->userId())
    					->first();
    	if (is_null($title)) {
    		$title = $this->createTitle();
    	}
    	return response()->json(['title' => $title], 200);
    }

    function createTitle() {
    	$title = Title::create([
    		'user_id' => $this->userId()
    	]);
    	return $title;
    }

    function updateImage(Request $request) {
        $title = Title::where('user_id', $this->userId())
                        ->first();
        $title->image_url = $request->image_url;
        $title->save();
        return response()->json(['title' => $title], 200); 
    }

    function update(Request $request) {
    	$title = Title::find($request->id);
    	$title->header = !empty($request->header) ? ucfirst($request->header) : null;
    	$title->lead = !empty($request->lead) ? ucfirst($request->lead) : null;
    	$title->save();
        return response()->json(['title' => $title], 200); 
    }
}
