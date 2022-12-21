<?php

namespace App\Http\Controllers;

use App\UpdateFeature;
use Illuminate\Http\Request;

class UpdateFeatureController extends Controller
{
    function index() {
        $models = UpdateFeature::all();
        return response()->json(['models' => $models], 200);
    }
}
