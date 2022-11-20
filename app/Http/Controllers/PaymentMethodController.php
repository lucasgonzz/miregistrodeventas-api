<?php

namespace App\Http\Controllers;

use App\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{

    public function index()
    {
        $models = PaymentMethod::where('user_id', $this->userId())
                                        ->get();
        return response()->json(['models' => $models], 200);
    }

    public function store(Request $request)
    {
        $model = PaymentMethod::create([
            'name'                      => $request->name,
            'description'               => $request->description,
            'payment_method_type_id'    => $request->payment_method_type_id,
            'public_key'                => $request->public_key,
            'access_token'              => $request->access_token,
            'user_id'                   => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    public function update(Request $request, $id)
    {
        $model = PaymentMethod::find($id);
        $model->name                   = $request->name;
        $model->description            = $request->description;
        $model->payment_method_type_id = $request->payment_method_type_id;
        $model->public_key             = $request->public_key;
        $model->access_token           = $request->access_token;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id)
    {
        $model = PaymentMethod::find($id);
        $model->delete();
        return response(null, 200);
    }
}
