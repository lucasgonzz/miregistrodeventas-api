<?php

namespace App\Http\Controllers;

use App\UserPayment;
use Illuminate\Http\Request;

class UserPaymentController extends Controller
{
    function index($model_id, $from_date, $until_date) {
        $models = UserPayment::where('user_id', $model_id)
                            ->whereDate('created_at', '>=', $from_date)
                            ->whereDate('created_at', '<=', $until_date)
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

    function update(Request $request, $id) {
        $model = UserPayment::find($id);
        $model->amount        = $request->amount;
        $model->description   = $request->description;
        $model->save();
        return response()->json(['model' => $model], 201);
    }

    function destroy($id) {
        $model = UserPayment::find($id);
        $model->delete();
        return response(null, 200);
    }
}
