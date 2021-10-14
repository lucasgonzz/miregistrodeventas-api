<?php

namespace App\Http\Controllers;

use App\Cupon;
use App\Http\Controllers\Helpers\CuponHelper;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    function index() {
        $cupons = Cupon::where('user_id', $this->userId())
                        ->where('valid', true)
                        ->with('buyer')
                        ->get();
        return response()->json(['cupons' => $cupons], 200);
    }

    function store(Request $request) {
        $cupons = [];
        foreach ($request['buyers'] as $buyer) {
            $cupon = Cupon::create([
                'amount'            => CuponHelper::getAmount($request),
                'percentage'        => CuponHelper::getPercentage($request),
                'expiration_date'   => CuponHelper::getExpirationDate($request),
                'buyer_id'          => $buyer['id'],
                'user_id'           => $this->userId(),
            ]);

            CuponHelper::sendCuponNotification($cupon);

            $cupons[] = Cupon::where('id', $cupon->id)
                            ->with('buyer')
                            ->first();
        }
        return response()->json(['cupons' => $cupons], 201);
    }

    function delete($id) {
        $cupon = Cupon::find($id);
        $cupon->delete();
        return response(null, 200);
    }
}
