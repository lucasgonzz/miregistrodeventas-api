<?php

namespace App\Http\Controllers;

use App\Extencion;
use Illuminate\Http\Request;

class ExtencionController extends Controller
{
    
    function index() {
        $models = Extencion::all();
        return response()->json(['models' => $models], 200);
    }

}
