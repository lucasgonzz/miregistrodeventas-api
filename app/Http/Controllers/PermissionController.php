<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

 	function index() {
 		$permissions = Permission::all();
 		return response()->json(['permissions' => $permissions], 200);
 	}   

}
