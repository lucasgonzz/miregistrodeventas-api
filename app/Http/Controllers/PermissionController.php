<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

 	function index() {
 		$permissions = Permission::whereNull('extencion_id')
 									->get();
 		return response()->json(['permissions' => $permissions], 200);
 	}   

 	function extencions() {
 		$permissions = [];
 		foreach (Auth()->user()->extencions as $extencion) {
 			$permissions[] = Permission::where('extencion_id', $extencion->id)
 										->get();
 		}
 		return response()->json(['permissions' => $permissions], 200);
 	}   

}
