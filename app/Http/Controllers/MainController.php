<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class MainController extends Controller
{
	function __construct() {
        $this->middleware('auth');
        $this->middleware('trial', ['except' => ['trialFinish', 'login']]);

        // Este middleware es para que el Auth() no me devuelva null
        // Esto hacia problema con el invitado recommended
        $this->middleware(function ($request, $next) {
            if (!Auth()->user()->hasRole('owner')) {
                $this->middleware('can:sale.create')->only('vender');
                $this->middleware('can:article.create')->only('ingresar');
                $this->middleware('can:article.index')->only('listado');
                $this->middleware('can:sale.index')->only('ventas');
                $this->middleware('can:employee')->only('empleados');
                $this->middleware('can:online')->only('online');
            }
            return $next($request);
        });
	}

    function offLine() {
        return view('modules/laravelpwa/offline');
    }

    function isDesktop() {
        $agent = new Agent();
        return [
            'is_desktop' => $agent->isDesktop(),
        ];
    }

	function index() {
		$user = Auth()->user();
        if ($user->status == 'super') {
            return redirect()->route('super');
        } else if ($user->status == 'admin') {
            return redirect()->route('admin');
        } else if ($user->status == 'without_use' || $user->status == 'trial') {
            return redirect()->route('vender');
        } else if ($user->hasPermissionTo('sale.create') || $user->hasRole('owner')) {
            return redirect()->route('vender');
        } else if ($user->hasPermissionTo('article.create')) {
            return redirect()->route('ingresar');
        } else if ($user->hasPermissionTo('article.index')) {
            return redirect()->route('listado');
        } else if ($user->hasPermissionTo('sale.index')) {
            return redirect()->route('ventas');
        }
	}

    function login() {
        return view('auth.login');
    }
    function super() {
        return view('main.super');
    }
    function admin() {
        return view('main.admin');
    }
    function vender() {
    	return view('main.vender');
    }
    function ingresar() {
        return view('main.ingresar');
    }
    function listado() {
        return view('main.listado');
    }
    function ventas() {
        return view('main.ventas');
    }
    function online() {
        return view('main.online');
    }
    function empleados() {
        return view('main.empleados');
    }
    function codigosDeBarra() {
        return view('main.codigos_de_barra');
    }
    function configuracion() {
        if (Auth()->user()->hasRole('owner')) {
            return view('main.configuracion');
        } else {
            return redirect('/');
        }
    }
    function trialFinish() {
        $user = Auth()->user();
        // Si el usuario contrata el servicio se cambia el status a in_use y se recarga la pagina de trial_finish, cunado se recargue el estado no va a ser trial, entonces se redirige
        if ($user->status != 'trial') {
            return redirect('/');
        } else {
            return view('main.trial_finish');
        }
    }

    // Comunes
}
