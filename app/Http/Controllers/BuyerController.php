<?php

namespace App\Http\Controllers;

use App\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    function index() {
        $models = Buyer::where('user_id', $this->userId())
                        ->orderBy('created_at', 'DESC')
                        ->withAll()
                        ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = Buyer::create([
            'name'                      => $request->name,
            'email'                     => $request->email,
            'phone'                     => $request->phone,
            'password'                  => bcrypt('1234'),
            'comercio_city_client_id'   => $request->id,
            'user_id'                   => $this->userId(),
        ]);
        return response()->json(['model' => $this->fullModel('App\Buyer', $model->id)], 201);
    }
    
    function destroy($id) {
        $model = Buyer::find($id);
        $model->delete();
        return response(null, 200);
    }
}
