<?php

namespace App\Http\Controllers;

use App\Icon;
use Illuminate\Http\Request;

class IconController extends Controller
{
    function index() {
        $icons = Icon::all();
        return response()->json(['icons' => $icons], 200);
    }
}
