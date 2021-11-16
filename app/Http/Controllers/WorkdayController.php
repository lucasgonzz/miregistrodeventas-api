<?php

namespace App\Http\Controllers;

use App\Workday;
use Illuminate\Http\Request;

class WorkdayController extends Controller
{
    function index() {
        $workdays = Workday::with(['schedules' => function($query) {
                                $query->where('user_id', $this->userId());
                            }])
                            ->get();
        return response()->json(['workdays' => $workdays], 200);
    }

    function removeSchedule(Request $request) {
        $workday = Workday::find($request->workday['id']);
        $workday->schedules()->detach($request->schedule['id']);
        $workday = Workday::where('id', $workday->id)
                            ->with('schedules')
                            ->first();
        return response()->json(['workday' => $workday], 200);
    }

    function addSchedule(Request $request) {
        $workday = Workday::find($request->workday['id']);
        $workday->schedules()->attach($request->schedule['id']);
        $workday = Workday::where('id', $workday->id)
                            ->with('schedules')
                            ->first();
        return response()->json(['workday' => $workday], 200);
    }
}
