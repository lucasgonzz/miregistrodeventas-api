<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\StringHelper;
use App\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{

    function index() {
        $schedules = Schedule::where('user_id', $this->userId())
                                ->get();
        return response()->json(['schedules' => $schedules], 200);
    }

    function store(Request $request) {
        $schedule = Schedule::create([
            'name'      => StringHelper::onlyFirstWordUpperCase($request->name),
            'from'      => $request->from,
            'to'        => $request->to,
            'user_id'   => $this->userId(),
        ]);
        return response()->json(['schedule' => $schedule], 201);
    }

    function update(Request $request) {
        $schedule = Schedule::find($request->id);
        $schedule->name = StringHelper::onlyFirstWordUpperCase($request->name);
        $schedule->from = $request->from;
        $schedule->to   = $request->to;
        $schedule->save();
        return response()->json(['schedule' => $schedule], 200);
    }
}
