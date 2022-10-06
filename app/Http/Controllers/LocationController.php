<?php

namespace App\Http\Controllers;

use App\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    
    public function index() {
        $models = Location::where('user_Id', $this->userId())
                            ->orderBy('name', 'ASC')
                            ->get();
        return response()->json(['models' => $models], 200);
    }


    public function store(Request $request) {
        $model = Location::create([
            'name'          => $request->name,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    
    public function update(Request $request, $id) {
        $model = Location::find($id);
        $model->name = $request->name;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = Location::find($id);
        $model->delete();
        return response(null, 200);
    }
}
