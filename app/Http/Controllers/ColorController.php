<?php

namespace App\Http\Controllers;

use App\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    function index() {
        $colors = Color::all();
        return response()->json(['colors' => $colors], 200);
    }
}
