<?php

namespace App\Http\Controllers;

use App\Condition;
use App\Http\Controllers\Helpers\StringHelper;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    function index() {
        $models = Condition::where('user_id', $this->userId())
                                ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = Condition::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 200); 
    }

    function update(Request $request) {
        $model = Condition::find($request->id);
        $model->name = $request->name;
        $model->description = $request->description;
        $model->save();
        return response()->json(['model' => $model], 200); 
    }

    function delete($id) {
        $model = Condition::find($id);
        $model->delete();
        return response(null, 200);
    }
}
