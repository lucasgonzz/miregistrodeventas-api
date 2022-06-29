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
        $cupon = Cupon::create([
            'amount'            => CuponHelper::getAmount($request),
            'percentage'        => CuponHelper::getPercentage($request),
            'min_amount'        => CuponHelper::getMinAmount($request),
            'code'              => $request->code,
            'user_id'           => $this->userId(),
            'type'              => 'normal',
        ]);
        return response()->json(['cupon' => $cupon], 201);
        $cupons = [];
        if (CuponHelper::isForNewBuyers($request)) {
            $cupon = Cupon::create([
                'amount'            => CuponHelper::getAmount($request),
                'percentage'        => CuponHelper::getPercentage($request),
                'min_amount'        => CuponHelper::getMinAmount($request),
                'expiration_days'   => CuponHelper::getExpirationDays($request),
                'user_id'           => $this->userId(),
                'type'              => 'for_new_buyers',
            ]);
            return response()->json(['cupons' => [$cupon]], 201);
        } else {
            foreach ($request['buyers'] as $buyer) {
                $cupon = Cupon::create([
                    'amount'            => CuponHelper::getAmount($request),
                    'percentage'        => CuponHelper::getPercentage($request),
                    'min_amount'        => CuponHelper::getMinAmount($request),
                    'expiration_date'   => CuponHelper::getExpirationDate($request),
                    'buyer_id'          => $buyer['id'],
                    'user_id'           => $this->userId(),
                ]);

                CuponHelper::sendCuponNotification($cupon);

                $cupons[] = Cupon::where('id', $cupon->id)
                                ->with('buyer')
                                ->first();
            }
        }
        return response()->json(['cupons' => $cupons], 201);
    }

    function delete($id) {
        $cupon = Cupon::find($id)->update(['valid' => 0]);
        return response(null, 200);
    }
}
