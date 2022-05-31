<?php

namespace App\Http\Controllers;

use App\Address;
use App\Http\Controllers\Helpers\StringHelper;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    function store(Request $request) {
        $address = Address::create([
            'street'        => $request->street,
            'street_number' => $request->street_number,
            'city'          => $request->city,
            'province'      => $request->province,
            'lat'           => $request->lat,
            'lng'           => $request->lng,
            'user_id'       => $this->userId(),
            'depto'         => $request->depto,
            'description'   => StringHelper::modelName($request->description),
        ]);
        return response()->json(['address' => $address], 201);
    }

    function delete($id) {
        $address = Address::find($id);
        $address->delete();
        return response(null, 200);
    }
}
