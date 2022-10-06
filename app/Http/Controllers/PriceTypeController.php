<?php

namespace App\Http\Controllers;

use App\PriceType;
use Illuminate\Http\Request;

class PriceTypeController extends Controller
{
    public function index() {
        $models = PriceType::orderBy('position', 'ASC')
                            ->where('user_id', $this->userId())
                            ->get();
        return response()->json(['models' => $models], 200);
    }

    public function store(Request $request) {
        $model = PriceType::create([
            'name'          => $request->name,
            'percentage'    => $request->percentage,
            'position'      => $request->position,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

   
    public function update(Request $request, $id) {
        $model = PriceType::find($id);
        $model->name        = $request->name;
        $model->percentage  = $request->percentage;
        $model->position    = $request->position;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    function updateImage(Request $request, $id) {
        $model = PriceType::find($id);
        if (!is_null($model->image_url)) {
            ImageHelper::deleteImage($model->image_url);
        }
        $model->image_url = $request->image_url;
        $model->save();
        return response()->json(['model' => $model], 200); 
    }

    public function destroy($id) {
        $model = PriceType::find($id);
        if (!is_null($model->image_url)) {
            $res = ImageHelper::deleteImage($model->image_url);
        }
        $model->delete();
        return response(null, 200);
    }
}
