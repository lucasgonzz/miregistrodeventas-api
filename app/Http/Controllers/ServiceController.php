<?php

namespace App\Http\Controllers;

use App\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    function store(Request $request) {
        $model = Service::create([
            'name'      => $request->name,
            'price'     => $request->price,
            'user_id'   => $this->userId(),
        ]);
        return response()->json(['service' => $model], 201);
    }
}
