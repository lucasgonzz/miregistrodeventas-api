<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\ImageHelper;
use App\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TitleController extends Controller
{
    function index() {
    	$titles = Title::where('user_id', $this->userId())
    					->get();
    	if (!count($titles) >= 1) {
    		$titles = $this->createTitle();
    	}
    	return response()->json(['models' => $titles], 200);
    }

    function createTitle() {
    	$title = Title::create([
    		'user_id' => $this->userId()
    	]);
    	return [$title];
    }

    function store() {
        $title = $this->createTitle()[0];
        return response()->json(['model' => $title], 200);
    }

    function updateImage(Request $request, $id) {
        $title = Title::find($id);
        $title->image_url = $request->image_url;
        $title->hosting_image_url = ImageHelper::saveHostingImage($request->image_url);
        $title->save();
        return response()->json(['model' => $title], 200); 
    }

    function update(Request $request) {
    	$title = Title::find($request->id);
    	$title->header = !empty($request->header) ? ucfirst($request->header) : null;
        $title->lead = !empty($request->lead) ? ucfirst($request->lead) : null;
    	$title->color = !empty($request->color) ? $request->color : null;
    	$title->save();
        return response()->json(['model' => $title], 200); 
    }

    function destroy($id) {
        $title = Title::find($id);
        $title->delete();
        return response(null, 200);
    }
}
