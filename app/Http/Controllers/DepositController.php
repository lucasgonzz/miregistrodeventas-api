<?php

namespace App\Http\Controllers;

use App\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
   
    public function index() {
        $models = Deposit::where('user_id', $this->userId())
                            ->get();
        return response()->json(['models' => $models], 200);
    }

    public function store(Request $request) {
        $model = Deposit::create([
            'name'          => $request->name,
            'description'   => $request->description,
            'user_id'       => $this->userId(),
        ]);
        return response()->json(['model' => $model], 201);
    }

    public function update(Request $request, $id) {
        $model = Deposit::find($id);
        $model->name        = $request->name;
        $model->description = $request->description;
        $model->save();
        return response()->json(['model' => $model], 200);
    }

    public function destroy($id) {
        $model = Deposit::find($id);
        $model->delete();
        return response(null, 200);
    }
    
}
