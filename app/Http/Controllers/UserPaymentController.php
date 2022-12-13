<?php

namespace App\Http\Controllers;

use App\UserPayment;
use Illuminate\Http\Request;

class UserPaymentController extends Controller
{
    function index($model_id, $from_date, $until_date) {
        $models = UserPayment::where('user_id', $model_id)
                            ->where('created_at', '>=', $from_date)
                            ->where('created_at', '<=', $until_date)
                            ->get();
        return response()->json(['models' => $models], 200);
    }

    function store(Request $request) {
        $model = UserPayment::create([
            'amount'        => $request->amount,
            'description'   => $request->description,
            'user_id'       => $request->model_id,
        ]);
        return response()->json(['model' => $model], 201);
    }
}
