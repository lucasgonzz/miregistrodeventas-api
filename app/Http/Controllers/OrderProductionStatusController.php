<?php

namespace App\Http\Controllers;

use App\OrderProductionStatus;
use Illuminate\Http\Request;

class OrderProductionStatusController extends Controller
{
    function index() {
        $models = OrderProductionStatus::where('user_id', $this->userId())
                                        ->orderBy('position', 'ASC')
                                        ->get();
        return response()->json(['models' => $models], 200);
    }

    public function store(Request $request) {
        $model = OrderProductionStatus::create([
            'name'      => $request->name,
            'position'  => $request->position,
            'user_id'   => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

   
    public function update(Request $request, $id) {
        $model = OrderProductionStatus::find($id);
        $model->name       = $request->name;
        $model->position   = $request->position;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = OrderProductionStatus::find($id);
        $model->delete();
        return response(null, 200);
    }
}
