<?php

namespace App\Http\Controllers;

use App\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    function index() {
        $sizes = Size::all();
        return response()->json(['sizes' => $sizes], 200);
    }
}
