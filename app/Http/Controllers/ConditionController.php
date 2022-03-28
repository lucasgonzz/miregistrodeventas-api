<?php

namespace App\Http\Controllers;

use App\Condition;
use App\Http\Controllers\Helpers\StringHelper;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    function index() {
        $conditions = Condition::where('user_id', $this->userId())
                                ->get();
        return response()->json(['conditions' => $conditions], 200);
    }

    function store(Request $request) {
        $condition = Condition::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['condition' => $condition], 200); 
    }

    function update(Request $request) {
        $condition = Condition::find($request->id);
        $condition->name = $request->name;
        $condition->description = $request->description;
        $condition->save();
        return response()->json(['condition' => $condition], 200); 
    }

    function delete($id) {
        $condition = Condition::find($id);
        $condition->delete();
        return response(null, 200);
    }
}
