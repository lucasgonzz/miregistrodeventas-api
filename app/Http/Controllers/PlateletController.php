<?php

namespace App\Http\Controllers;

use App\Platelet;
use Illuminate\Http\Request;

class PlateletController extends Controller
{
    
    public function index() {
        $models = Platelet::where('user_Id', $this->userId())
                                ->orderBy('created_at', 'DESC')
                                ->get();
        return response()->json(['models' => $models], 200);
    }


    public function store(Request $request) {
        $model = Platelet::create([
            'name'    => $request->name,
            'description'     => $request->description,
            'user_id'  => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    
    public function update(Request $request, $id) {
        $model = Platelet::find($id);
        $model->name = $request->name;
        $model->description = $request->description;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = Platelet::find($id);
        $model->delete();
        return response(null, 200);
    }
}
