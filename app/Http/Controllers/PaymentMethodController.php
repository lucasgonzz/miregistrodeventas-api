<?php

namespace App\Http\Controllers;

use App\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{

    public function index()
    {
        $payment_methods = PaymentMethod::where('user_id', $this->userId())
                                        ->get();
        return response()->json(['payment_methods' => $payment_methods], 200);
    }

    public function store(Request $request)
    {
        $payment_method = PaymentMethod::create([
            'name'                      => $request->name,
            'description'               => $request->description,
            'payment_method_type_id'    => $request->payment_method_type_id,
            'public_key'                => $request->public_key,
            'access_token'              => $request->access_token,
            'user_id'                   => $this->userId(),
        ]);
        return response()->json(['payment_method' => $payment_method], 201);
    }

    public function update(Request $request, $id)
    {
        $payment_method = PaymentMethod::find($id);
        $payment_method->name                   = $request->name;
        $payment_method->description            = $request->description;
        $payment_method->payment_method_type_id = $request->payment_method_type_id;
        $payment_method->public_key             = $request->public_key;
        $payment_method->access_token           = $request->access_token;
        $payment_method->save();
        return response()->json(['payment_method' => $payment_method], 200);
    }

    public function destroy($id)
    {
        $payment_method = PaymentMethod::find($id);
        $payment_method->delete();
        return response(null, 200);
    }
}
