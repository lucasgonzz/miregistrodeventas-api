<?php

namespace App\Http\Controllers;

use App\Surchage;
use Illuminate\Http\Request;

class SurchageController extends Controller
{
    function index() {
        $models = Surchage::where('user_id', $this->userId())
                        ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = Surchage::create([
            'name'          => $request->name,
            'percentage'    => $request->percentage,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    function update(Request $request, $id) {
        $model = Surchage::find($id);
        $model->name = $request->name;
        $model->percentage = $request->percentage;
        $model->save();
        return response()->json(['model' => $model], 201);
    }

    function destroy(Request $request, $id) {
        $model = Surchage::find($id);
        $model->delete();
        return response(null, 200);
    }
}
