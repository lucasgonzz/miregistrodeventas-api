<?php

namespace App\Http\Controllers;

use App\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    function index() {
        $calls = Call::where('user_id', $this->userId())
                        ->where('status', 'unrealized')
                        ->with('buyer')
                        ->get();
        return response()->json(['calls' => $calls], 200);
    }

    function realized(Request $request) {
        $call = Call::find($request->id);
        $call->status = 'realized';
        $call->save();
        return response(null, 200);
    }
}
