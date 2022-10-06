<?php

namespace App\Http\Controllers;

use App\Iva;
use Illuminate\Http\Request;

class IvaController extends Controller
{
    function index() {
        $ivas = Iva::all();
        return response()->json(['models' => $ivas], 200);
    }
}
