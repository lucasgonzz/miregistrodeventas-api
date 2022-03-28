<?php

namespace App\Http\Controllers;

use App\Brand;
use App\Http\Controllers\Helpers\StringHelper;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    
    function index() {
        $brands = Brand::where('user_id', $this->userId())
                        ->get();
        return response()->json(['brands' => $brands], 200);
    }

    function store(Request $request) {
        $brand = Brand::create([
            'name'      => StringHelper::modelName($request->name),
            'user_id'   => $this->userId(),
        ]);
        return response()->json(['brand' => $brand], 200); 
    }

    function update(Request $request) {
        $brand = Brand::find($request->id);
        $brand->name = StringHelper::modelName($request->name);
        $brand->save();
        return response()->json(['brand' => $brand], 200); 
    }

    function delete($id) {
        $brand = Brand::find($id);
        $brand->delete();
        return response(null, 200);
    }
}
