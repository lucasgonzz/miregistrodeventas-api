<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class SuperUserController extends Controller
{

    function index() {
        $models = User::where('status', 'commerce')
                    ->whereNull('owner_id')
                    ->withAll()
                    ->get();
        return response()->json(['models' => $models], 200);
    }

    function update(Request $request, $id) {
        $model = User::find($id);
        $model->name = $request->name;
        $model->company_name = $request->company_name;
        $this->setExtencions($model, $request->extencions_id);
        return response()->json(['model' => $this->fullModel('App\User', $model->id)], 200);
    }

    function setExtencions($model, $extencions_id) {
        $model->extencions()->detach();
        foreach ($extencions_id as $id) {
            $model->extencions()->attach($id);
        }
    }
}
