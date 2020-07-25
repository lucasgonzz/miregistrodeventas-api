<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Caffeinated\Shinobi\Models\Permission;

class PermissionController extends Controller
{

    // Retorna los permisos para darle a los empleados
 	function index() {
 		$permissions = Permission::whereNull('user_id')
 									->get();
 		return $permissions;
 	}   

 	function canUse() {
 		return Permission::where('user_id', 0)
 							->get();
 	}

 	function saleTime() {
 		$permissions = Permission::where('user_id', $this->UserId())
 									->get();
 		return $permissions;
 	}
}
