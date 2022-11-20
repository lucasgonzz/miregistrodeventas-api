<?php

namespace App\Http\Controllers;

use App\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    
    public function index() {
        $models = DeliveryZone::where('user_Id', $this->userId())
                                ->orderBy('created_at', 'DESC')
                                ->get();
        return response()->json(['models' => $models], 200);
    }


    public function store(Request $request) {
        $model = DeliveryZone::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'price'         => $request->price,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    
    public function update(Request $request, $id) {
        $model = DeliveryZone::find($id);
        $model->name = $request->name;
        $model->description = $request->description;
        $model->price = $request->price;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = DeliveryZone::find($id);
        $model->delete();
        return response(null, 200);
    }
}
