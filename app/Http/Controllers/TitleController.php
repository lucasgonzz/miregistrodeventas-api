<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Title;
use Illuminate\Support\Facades\Log;

class TitleController extends Controller
{
    function index() {
    	$titles = Title::where('user_id', $this->userId())
    					->get();
    	if (!count($titles) >= 1) {
    		$titles = $this->createTitle();
    	}
    	return response()->json(['titles' => $titles], 200);
    }

    function createTitle() {
    	$title = Title::create([
    		'user_id' => $this->userId()
    	]);
    	return [$title];
    }

    function store() {
        $title = $this->createTitle()[0];
        return response()->json(['title' => $title], 200);
    }

    function updateImage(Request $request, $id) {
        Log::info('asdasd: ');
        Log::info('Id: '.$id);
        $title = Title::find($id);
        $title->image_url = $request->image_url;
        $title->save();
        return response()->json(['title' => $title], 200); 
    }

    function update(Request $request) {
    	$title = Title::find($request->id);
    	$title->header = !empty($request->header) ? ucfirst($request->header) : null;
        $title->lead = !empty($request->lead) ? ucfirst($request->lead) : null;
    	$title->color = !empty($request->color) ? $request->color : null;
    	$title->save();
        return response()->json(['title' => $title], 200); 
    }

    function delete($id) {
        $title = Title::find($id);
        $title->delete();
        return response(null, 200);
    }
}
