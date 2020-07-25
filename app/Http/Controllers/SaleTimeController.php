<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SaleTime;
use Caffeinated\Shinobi\Models\Permission;

class SaleTimeController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    |
    | Se pone showInModal para que es fecha se muestre en el modal de todos los sale_times
    | En la vista se fija si la primer fecha tiene el from mas grande que el to y se lo
    | pone denuevo en el final, con showInModal en false para que solo aparesca en el 
    | saleTimesNav
    |
    */
    function index() {
        $sale_times = SaleTime::where('user_id', $this->userId())
                        ->get();
        $sale_times_result = [];
        foreach ($sale_times as $sale_time) {
            if ($sale_time->from > $sale_time->to) {
                $sale_time->from = date('H:i', strtotime($sale_time->from));
                $sale_time->to = date('H:i', strtotime($sale_time->to));
                $sale_time->showInModal = true;
                $sale_times_result[] = $sale_time;
                break;
            }
        }
        foreach ($sale_times as $sale_time) {
            if ($sale_time->from < $sale_time->to) {
                $sale_time->from = date('H:i', strtotime($sale_time->from));
                $sale_time->to = date('H:i', strtotime($sale_time->to));
                $sale_time->showInModal = true;
                $sale_times_result[] = $sale_time;
            }
        }
        return $sale_times_result;
    }

    function delete($id) {
        $sale_time = SaleTime::find($id);
        $permission = Permission::where('user_id', $this->userId())
                                    ->where('name', $sale_time->name)
                                    ->first();
        $permission->delete();
        $sale_time->delete();
    }

    function store(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $ok = true;
        if ($from > $to) {
            $sale_times = SaleTime::where('user_id', Auth()->user()->id)
                        ->get();
            foreach ($sale_times as $sale_time) {
                if ($sale_time->from > $sale_time->to) {
                    $ok = false;
                }
            }
        }
        if ($ok) {
        	SaleTime::create([
        		'user_id' => $this->userId(),
        		'name' => ucwords($request->name),
        		'from' => $from,
        		'to' => $to,
        	]);
            $permission = Permission::create([
                'user_id' => $this->userId(),
                'name' => ucwords($request->name),
                'slug' => 'sale.create.'.strtolower(str_replace('ñ', 'n', $request->name)),
                'description' => 'Podra ver las ventas entre las '.$from.' y '.$to,
            ]);
            $user = Auth()->user();
            foreach ($user->employees as $employee) {
                if (!$this->hasPermissionToShowAnySale($employee)) {
                    // Si al momento de crear este horario de venta el empleado no tiene permiso para ver ningun horario de venta se le da el permiso de ver todas
                    // 7 es el id del permiso para ver todas las ventas
                    $employee->permissions()->attach(7);
                } 
            }
        }
        return [
            'ok' => $ok,
        ];
    }

    function hasPermissionToShowAnySale($employee) {
        if ($employee->hasPermissionTo('sale.index.all')) {
            return true;
        }
        $permissions_sale_time = Permission::where('user_id', Auth()->user()->id)
                                            ->get();
        foreach ($permissions_sale_time as $permission_sale_time) {
            foreach ($employee->permissions as $permission) {
                if ($permission->id == $permission_sale_time->id) {
                    return true;
                }
            }
        }
        return false;
    }
}
